<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private NotificationService $notificationService) {}

    public function create(ServiceOrder $order, array $data, ?UploadedFile $proof = null): Payment
    {
        return DB::transaction(function () use ($order, $data, $proof) {
            $lockedOrder = ServiceOrder::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'completed') {
                throw new \RuntimeException("Pembayaran hanya dapat diajukan untuk service order yang sudah selesai (status saat ini: '{$lockedOrder->status}').");
            }

            if ($lockedOrder->grand_total <= 0) {
                throw new \RuntimeException('Total tagihan servis tidak valid (grand total: 0).');
            }

            $hasActivePayment = Payment::where('service_order_id', $lockedOrder->id)
                ->whereIn('status', ['waiting_verification', 'verified', 'paid'])
                ->lockForUpdate()
                ->exists();

            if ($hasActivePayment) {
                throw new \RuntimeException('Pembayaran untuk order ini sudah diajukan atau diverifikasi.');
            }

            // BR-014: amount must match grand_total from DB (never from request)
            $amount = $lockedOrder->grand_total;

            $proofPath = null;
            if ($proof) {
                $proofPath = $proof->store('payments/proofs', 'local');
            }

            // paid_at TIDAK diisi saat submit pembayaran; hanya saat dikonfirmasi/diverifikasi
            $payment = Payment::create([
                'service_order_id' => $lockedOrder->id,
                'payment_method' => $data['payment_method'],
                'amount' => $amount,
                'proof_path' => $proofPath,
                'paid_at' => null,
                'status' => 'waiting_verification',
                'notes' => $data['notes'] ?? null,
            ]);

            if ($lockedOrder->booking && $lockedOrder->booking->canTransitionTo('waiting_payment')) {
                $lockedOrder->booking->update(['status' => 'waiting_payment']);
            }

            AuditLog::record('payment.created', 'Payment', $payment->id, [
                'amount' => $amount,
                'method' => $data['payment_method'],
            ]);

            if ($proofPath) {
                AuditLog::record('payment.proof_uploaded', 'Payment', $payment->id, [
                    'proof_path' => $proofPath,
                ]);
            }

            if ($data['payment_method'] === 'cash') {
                AuditLog::record('payment.cash_payment_confirmed', 'Payment', $payment->id, [
                    'amount' => $amount,
                ]);
            }

            $admins = User::where('role', 'admin')->where('is_active', true)->get();
            $this->notificationService->notifyMany(
                $admins->all(),
                'Pembayaran Menunggu Verifikasi',
                "Payment untuk order #{$lockedOrder->id} menunggu verifikasi.",
                'payment',
                'Payment',
                $payment->id
            );

            return $payment;
        });
    }

    public function verify(Payment $payment, int $adminId): Payment
    {
        return DB::transaction(function () use ($payment, $adminId) {
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status !== 'waiting_verification') {
                throw new \RuntimeException("Tidak dapat verifikasi payment dari status '{$lockedPayment->status}'.");
            }

            $lockedPayment->update([
                'status' => 'verified',
                'verified_by' => $adminId,
                'verified_at' => now(),
                'paid_at' => now(),
            ]);

            $booking = $lockedPayment->serviceOrder?->booking;
            if ($booking) {
                if ($booking->canTransitionTo('waiting_payment')) {
                    $booking->update(['status' => 'waiting_payment']);
                }
                if ($booking->canTransitionTo('paid')) {
                    $booking->update(['status' => 'paid']);
                }

                $hasActiveDelivery = $booking->deliveryTasks()
                    ->where('type', 'delivery')
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->exists();

                if ($hasActiveDelivery) {
                    if ($booking->canTransitionTo('ready_for_delivery')) {
                        $booking->update(['status' => 'ready_for_delivery']);
                    }
                } else {
                    if ($booking->canTransitionTo('completed')) {
                        $booking->update(['status' => 'completed']);
                    }
                }
            }

            if ($booking?->customer) {
                $this->notificationService->notify(
                    $booking->customer,
                    'Pembayaran Diverifikasi',
                    "Pembayaran untuk booking #{$booking->nomor_booking} telah diverifikasi.",
                    'payment',
                    'Payment',
                    $lockedPayment->id
                );
            }

            AuditLog::record('payment.verified', 'Payment', $lockedPayment->id, ['admin_id' => $adminId]);

            return $lockedPayment->fresh();
        });
    }

    public function reject(Payment $payment, int $adminId, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $adminId, $reason) {
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status !== 'waiting_verification') {
                throw new \RuntimeException("Tidak dapat menolak payment dari status '{$lockedPayment->status}'.");
            }

            $lockedPayment->update([
                'status' => 'rejected',
                'verified_by' => $adminId,
                'verified_at' => now(),
                'notes' => $reason,
            ]);

            $booking = $lockedPayment->serviceOrder?->booking;
            if ($booking?->customer) {
                $this->notificationService->notify(
                    $booking->customer,
                    'Pembayaran Ditolak',
                    "Pembayaran untuk order #{$lockedPayment->service_order_id} ditolak: {$reason}. Silakan ajukan ulang pembayaran.",
                    'payment',
                    'Payment',
                    $lockedPayment->id
                );
            }

            AuditLog::record('payment.rejected', 'Payment', $lockedPayment->id, ['reason' => $reason]);

            return $lockedPayment->fresh();
        });
    }
}
