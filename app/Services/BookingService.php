<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\DeliveryTask;
use App\Models\ServiceOrder;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private MechanicAssignmentService $mechanicAssignment,
        private DeliveryFeeService $deliveryFee,
        private NotificationService $notificationService,
    ) {}

    public function create(array $data, int $customerId): Booking
    {
        return DB::transaction(function () use ($data, $customerId) {
            $booking = Booking::create([
                ...$data,
                'customer_id'    => $customerId,
                'nomor_booking'  => Booking::generateNomorBooking(),
                'status'         => 'pending',
            ]);

            // Create pickup delivery task if requested
            if ($booking->pickup_requested && $booking->alamat_pickup) {
                $fee = $this->deliveryFee->calculate($booking->estimated_distance_km ?? 0);
                $pickupTask = DeliveryTask::create([
                    'booking_id'   => $booking->id,
                    'courier_id'   => null,
                    'type'         => 'pickup',
                    'address'      => $booking->alamat_pickup,
                    'distance_km'  => $booking->estimated_distance_km,
                    'delivery_fee' => $fee,
                    'status'       => 'pending',
                ]);

                AuditLog::record('delivery_task.created', 'delivery_task', $pickupTask->id, [
                    'type' => 'pickup',
                    'booking_id' => $booking->id,
                ]);
            }

            AuditLog::record('booking.created', 'booking', $booking->id, [
                'nomor_booking' => $booking->nomor_booking,
                'customer_id'   => $customerId,
            ]);

            $this->notificationService->notify(
                $booking->customer,
                'Booking Dibuat',
                "Booking #{$booking->nomor_booking} berhasil dibuat. Menunggu konfirmasi admin.",
                'booking',
                'Booking',
                $booking->id
            );

            return $booking;
        });
    }

    public function confirm(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $this->transitionStatus($booking, 'confirmed');
            
            $isPickup = (bool) $booking->pickup_requested;
            
            if ($isPickup) {
                $this->transitionStatus($booking, 'waiting_pickup');
                // STOP here, do not assign mechanic yet for pickup
            } else {
                $this->transitionStatus($booking, 'waiting_service');
                // Auto-assign mechanic for non-pickup
                $this->assignMechanic($booking);
            }

            $this->notificationService->notify(
                $booking->customer,
                'Booking Dikonfirmasi',
                "Booking #{$booking->nomor_booking} telah dikonfirmasi.",
                'booking',
                'Booking',
                $booking->id
            );

            return $booking->fresh();
        });
    }

    public function arriveAtWorkshop(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $this->transitionStatus($booking, 'waiting_service');
            $this->assignMechanic($booking);
            return $booking->fresh();
        });
    }

    public function assignMechanic(Booking $booking): bool
    {
        if ($booking->status !== 'waiting_service') {
            return false;
        }

        $mechanic = $this->mechanicAssignment->assign($booking);
        if ($mechanic) {
            ServiceOrder::create([
                'booking_id'  => $booking->id,
                'mechanic_id' => $mechanic->id,
                'status'      => 'pending',
            ]);
            $this->transitionStatus($booking, 'assigned');

            AuditLog::record('mechanic.assigned', 'booking', $booking->id, [
                'mechanic_id' => $mechanic->id,
            ]);

            $this->notificationService->notify(
                $mechanic,
                'Pekerjaan Baru',
                "Pekerjaan baru: Booking #{$booking->nomor_booking}",
                'job',
                'Booking',
                $booking->id
            );
            return true;
        }
        return false;
    }

    public function cancel(Booking $booking, string $reason = ''): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            if (!$booking->canTransitionTo('cancelled')) {
                throw new \RuntimeException('Booking tidak dapat dibatalkan pada status ini.');
            }
            $booking->update([
                'status'              => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            AuditLog::record('booking.cancelled', 'booking', $booking->id, ['reason' => $reason]);

            $this->notificationService->notify(
                $booking->customer,
                'Booking Dibatalkan',
                "Booking #{$booking->nomor_booking} telah dibatalkan.",
                'booking',
                'Booking',
                $booking->id
            );

            return $booking->fresh();
        });
    }

    private function transitionStatus(Booking $booking, string $newStatus): void
    {
        if (!$booking->canTransitionTo($newStatus)) {
            throw new \RuntimeException("Tidak dapat mengubah status dari '{$booking->status}' ke '{$newStatus}'.");
        }
        $booking->update(['status' => $newStatus]);
    }

    /**
     * Ambil daftar booking sesuai role (admin lihat semua, customer lihat miliknya).
     */
    public function listFor(\App\Models\User $user, int $perPage = 15)
    {
        return $user->isAdmin()
            ? Booking::with(['customer', 'vehicle'])->latest()->paginate($perPage)
            : Booking::forCustomer($user->id)->with('vehicle')->latest()->paginate($perPage);
    }

}