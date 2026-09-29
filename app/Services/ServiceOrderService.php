<?php

namespace App\Services;

use App\Models\ServiceOrder;
use App\Models\ServiceItem;
use App\Models\ServiceOrderItem;
use App\Models\Booking;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class ServiceOrderService
{
    public function __construct(private \App\Services\NotificationService $notificationService) {}

    /**
     * BR-004: Harga service berasal dari database.
     * BR-005: Mechanic tidak boleh mengubah harga.
     * BR-009: Invoice menggunakan snapshot harga transaksi.
     * BR-014: Total transaksi selalu dihitung ulang oleh backend.
     */
    public function addItem(ServiceOrder $order, int $serviceItemId, int $quantity): ServiceOrderItem
    {
        return DB::transaction(function () use ($order, $serviceItemId, $quantity) {
            // ALWAYS fetch price from DB — never trust frontend
            $item = ServiceItem::active()->findOrFail($serviceItemId);

            // Check stock for spareparts
            if ($item->is_sparepart && $item->stock !== null && $item->stock < $quantity) {
                throw new \RuntimeException("Stok {$item->name} tidak mencukupi. Tersedia: {$item->stock}");
            }

            $subtotal = $item->price * $quantity;

            $orderItem = ServiceOrderItem::create([
                'service_order_id'   => $order->id,
                'service_item_id'    => $serviceItemId,
                'item_name_snapshot' => $item->name,   // snapshot
                'price_snapshot'     => $item->price,  // snapshot
                'quantity'           => $quantity,
                'subtotal'           => $subtotal,
            ]);

            // Reduce stock if sparepart
            if ($item->is_sparepart && $item->stock !== null) {
                $item->decrement('stock', $quantity);
            }

            // Recalculate totals
            $order->recalculate();

            AuditLog::record('service_order.item_added', 'ServiceOrder', $order->id, [
                'item_id'  => $serviceItemId,
                'quantity' => $quantity,
                'price'    => $item->price,
            ]);

            return $orderItem;
        });
    }

    public function removeItem(ServiceOrder $order, ServiceOrderItem $item): void
    {
        DB::transaction(function () use ($order, $item) {
            // Restore stock
            $serviceItem = $item->serviceItem;
            if ($serviceItem && $serviceItem->is_sparepart && $serviceItem->stock !== null) {
                $serviceItem->increment('stock', $item->quantity);
            }

            $item->delete();
            $order->recalculate();
        });
    }

    public function saveDiagnosis(ServiceOrder $order, string $diagnosis, ?string $notes = null): void
    {
        $order->update([
            'diagnosis_mechanic' => $diagnosis,
            'notes'              => $notes,
        ]);

        AuditLog::record('service_order.diagnosis_saved', 'ServiceOrder', $order->id);
    }

    public function startService(ServiceOrder $order): void
    {
        if ($order->status !== 'pending') {
            throw new \RuntimeException("Tidak dapat memulai servis dari status '{$order->status}'.");
        }

        $order->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        $order->booking->update(['status' => 'in_service']);

        AuditLog::record('service_order.started', 'ServiceOrder', $order->id);

        $this->notificationService->notify(
            $order->booking->customer,
            'Kendaraan Mulai Diservis',
            "Kendaraan Anda untuk booking #{$order->booking->nomor_booking} mulai diservis.",
            'service_order',
            'ServiceOrder',
            $order->id
        );
    }

    public function completeService(ServiceOrder $order): void
    {
        if ($order->status !== 'in_progress') {
            throw new \RuntimeException("Tidak dapat menyelesaikan servis dari status '{$order->status}'.");
        }

        if (empty($order->diagnosis_mechanic)) {
            throw new \RuntimeException('BR-012: Diagnosis mekanik harus diisi sebelum menyelesaikan servis.');
        }

        if ($order->items()->count() === 0) {
            throw new \RuntimeException('BR-012: Minimal satu tindakan servis harus dipilih.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            $order->booking->update(['status' => 'service_completed']);

            AuditLog::record('service_order.completed', 'ServiceOrder', $order->id, [
                'grand_total' => $order->grand_total,
            ]);

            $this->notificationService->notify(
                $order->booking->customer,
                'Servis Selesai',
                "Servis untuk booking #{$order->booking->nomor_booking} telah selesai.",
                'service_order',
                'ServiceOrder',
                $order->id
            );
        });
    }

    /**
     * Ambil daftar service order sesuai role (mekanik: miliknya, admin: semua).
     */
    public function listFor(\App\Models\User $user, int $perPage = 15)
    {
        $query = ServiceOrder::with(['booking.vehicle', 'mechanic']);

        if ($user->isMechanic()) {
            $query->where('mechanic_id', $user->id);
        } elseif (!$user->isAdmin()) {
            $query->whereHas('booking', fn ($q) => $q->where('customer_id', $user->id));
        }

        return $query->latest()->paginate($perPage);
    }

}