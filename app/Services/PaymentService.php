<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private NotificationService $notificationService) {}

    public function create(ServiceOrder $order, array $data, ?UploadedFile $proof = null): Payment
    {
        if ($order->status !== 'completed') {
            throw new \RuntimeException("Pembayaran hanya dapat diajukan untuk service order yang sudah selesai (status saat ini: '{$order->status}').");
        }

        if ($order->grand_total <= 0) {
            throw new \RuntimeException("Total tagihan servis tidak valid (grand total: 0).");
        }

        $hasActivePayment = Payment::where('service_order_id', $order->id)
            ->whereIn('status', ['waiting_verification', 'verified'])
            ->exists();
        if ($hasActivePayment) {
            throw new \RuntimeException("Pembayaran untuk order ini sudah diajukan atau diverifikasi.");
        }

        return DB::transaction(function () use ($order, $data, $proof) {
            // BR-014: amount must match grand_total from DB
            $amount = $order->grand_total;

            $proofPath = null;
            if ($proof) {
                $proofPath = $proof->store('payments/proofs', 'local');
            }

            $payment = Payment::create([
                'service_order_id' => $order->id,
                'payment_method'   => $data['payment_method'],
                'amount'           => $amount,
                'proof_path'       => $proofPath,
                'paid_at'          => now(),
                'status'           => 'waiting_verification',
                'notes'            => $data['notes'] ?? null,
            ]);

            if ($order->booking && $order->booking->canTransitionTo('waiting_payment')) {
                $order->booking->update(['status' => 'waiting_payment']);
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

            $admins = \App\Models\User::where('role', 'admin')->where('is_active', true)->get();
            $this->notificationService->notifyMany(
                $admins->all(),
                'Pembayaran Menunggu Verifikasi',
                "Payment untuk order #{$order->id} menunggu verifikasi.",
                'payment'
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
                'status'      => 'verified',
                'verified_by' => $adminId,
                'verified_at' => now(),
            ]);

            $booking = $lockedPayment->serviceOrder->booking;
            if ($booking) {
                if ($booking->canTransitionTo('waiting_payment')) {
                    $booking->update(['status' => 'waiting_payment']);
                }
                if ($booking->canTransitionTo('paid')) {
                    $booking->update(['status' => 'paid']);
                }
                if ($booking->canTransitionTo('completed')) {
                    $booking->update(['status' => 'completed']);
                }
                if ($booking->status !== 'completed') {
                    $booking->update(['status' => 'completed']);
                }
            }

            $this->notificationService->notify(
                $booking->customer,
                'Pembayaran Diverifikasi',
                "Pembayaran untuk booking #{$booking->nomor_booking} telah diverifikasi.",
                'payment',
                'Payment',
                $lockedPayment->id
            );

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
                'status'      => 'rejected',
                'verified_by' => $adminId,
                'verified_at' => now(),
                'notes'       => $reason,
            ]);

            AuditLog::record('payment.rejected', 'Payment', $lockedPayment->id, ['reason' => $reason]);

            return $lockedPayment->fresh();
        });
    }

}
