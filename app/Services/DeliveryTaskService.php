<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DeliverySetting;
use App\Models\DeliveryTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeliveryTaskService
{
    public function __construct(
        private DeliveryFeeService $deliveryFeeService,
        private \App\Services\NotificationService $notificationService
    ) {
    }

    /**
     * Assign courier ke task. Tidak mengubah fee — fee sudah dihitung
     * & disimpan saat task dibuat (BR-007/BR-014).
     */
    public function assignCourier(DeliveryTask $task, User $courier): DeliveryTask
    {
        return DB::transaction(function () use ($task, $courier) {
            $task->update([
                'courier_id' => $courier->id,
                'status'     => 'assigned',
            ]);

            AuditLog::record('delivery_task.assigned', 'delivery_task', $task->id, [
                'courier_id' => $courier->id,
            ]);

            $this->notificationService->notify(
                $courier,
                'Tugas Baru',
                'Anda ditugaskan untuk ' . ($task->type === 'pickup' ? 'menjemput' : 'mengantar') . " kendaraan booking #{$task->booking->nomor_booking}.",
                'delivery_task',
                'DeliveryTask',
                $task->id
            );

            return $task->fresh();
        });
    }

    /**
     * Kurir mulai memproses task (pickup atau delivery).
     */
    public function start(DeliveryTask $task): DeliveryTask
    {
        if (!in_array($task->status, ['pending', 'assigned'])) {
            throw new \RuntimeException("Tidak dapat memulai task dari status '{$task->status}'.");
        }

        return DB::transaction(function () use ($task) {
            $task->update([
                'status'     => 'in_progress',
                'started_at' => now(),
            ]);

            AuditLog::record('delivery.started', 'delivery_task', $task->id, [
                'type' => $task->type,
            ]);

            return $task->fresh();
        });
    }

    /**
     * Selesaikan task. Update status Booking sesuai jenis task:
     * pickup -> vehicle_picked_up, delivery -> completed.
     */
    public function complete(DeliveryTask $task): DeliveryTask
    {
        if ($task->status !== 'in_progress') {
            throw new \RuntimeException("Tidak dapat menyelesaikan task dari status '{$task->status}'.");
        }

        return DB::transaction(function () use ($task) {
            $task->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            $booking = $task->booking;

            if ($task->type === 'pickup') {
                $booking->update(['status' => 'vehicle_picked_up']);
                AuditLog::record('delivery.pickup_completed', 'delivery_task', $task->id, [
                    'booking_id' => $booking->id,
                ]);
            } else {
                $booking->update(['status' => 'completed']);
                AuditLog::record('delivery.completed', 'delivery_task', $task->id, [
                    'booking_id' => $booking->id,
                ]);

                $this->notificationService->notify(
                    $booking->customer,
                    'Delivery Selesai',
                    "Kendaraan untuk booking #{$booking->nomor_booking} telah sampai.",
                    'delivery_task',
                    'DeliveryTask',
                    $task->id
                );
            }

            return $task->fresh();
        });
    }

    /**
     * Hitung fee dari tarif resmi database. Tidak duplikasi logic —
     * delegasi penuh ke DeliveryFeeService yang sudah ada.
     */
    public function calculateFee(float $distanceKm): float
    {
        return $this->deliveryFeeService->calculate($distanceKm);
    }

    /**
     * Update tarif resmi (admin only, dicek di Form Request/Controller).
     * Tarif lama pada transaksi historis TIDAK berubah karena delivery_fee
     * sudah di-snapshot per task saat dibuat.
     */
    public function updateTariff(float $newPricePerKm): void
    {
        DB::transaction(function () use ($newPricePerKm) {
            $old = DeliverySetting::get('price_per_km', 3000);

            DeliverySetting::set('price_per_km', (string) $newPricePerKm);

            AuditLog::record('delivery.tariff_changed', 'delivery_setting', null, [
                'old_price_per_km' => $old,
                'new_price_per_km' => $newPricePerKm,
            ]);
        });
    }

    public function listFor(User $user, int $perPage = 15)
    {
        $query = DeliveryTask::with(['booking.vehicle', 'courier']);

        if ($user->isCourier()) {
            $query->where('courier_id', $user->id);
        } elseif (!$user->isAdmin()) {
            $query->whereHas('booking', fn ($q) => $q->where('customer_id', $user->id));
        }

        return $query->latest()->paginate($perPage);
    }
}
