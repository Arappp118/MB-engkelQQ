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
        return DB::transaction(function () use ($order, $data, $proof) {
            // BR-014: amount must match grand_total from DB
            $amount = $order->grand_total;

            $proofPath = null;
            if ($proof) {
                $proofPath = $proof->store('payments/proofs', 'local');
            }

            $status = $data['payment_method'] === 'cash' ? 'waiting_verification' : 'waiting_verification';

            $payment = Payment::create([
                'service_order_id' => $order->id,
                'payment_method'   => $data['payment_method'],
                'amount'           => $amount,
                'proof_path'       => $proofPath,
                'paid_at'          => now(),
                'status'           => $status,
                'notes'            => $data['notes'] ?? null,
            ]);

            $order->booking->update(['status' => 'waiting_payment']);

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
        if ($payment->status !== 'waiting_verification') {
            throw new \RuntimeException("Tidak dapat verifikasi payment dari status '{$payment->status}'.");
        }

        return DB::transaction(function () use ($payment, $adminId) {
            $payment->update([
                'status'      => 'verified',
                'verified_by' => $adminId,
                'verified_at' => now(),
            ]);

            $booking = $payment->serviceOrder->booking;
            $booking->update(['status' => 'paid']);
            $booking->update(['status' => 'completed']);

            $this->notificationService->notify(
                $booking->customer,
                'Pembayaran Diverifikasi',
                "Pembayaran untuk booking #{$booking->nomor_booking} telah diverifikasi.",
                'payment',
                'Payment',
                $payment->id
            );

            AuditLog::record('payment.verified', 'Payment', $payment->id, ['admin_id' => $adminId]);

            return $payment->fresh();
        });
    }

    public function reject(Payment $payment, int $adminId, string $reason): Payment
    {
        if ($payment->status !== 'waiting_verification') {
            throw new \RuntimeException("Tidak dapat menolak payment dari status '{$payment->status}'.");
        }

        $payment->update([
            'status'      => 'rejected',
            'verified_by' => $adminId,
            'verified_at' => now(),
            'notes'       => $reason,
        ]);

        AuditLog::record('payment.rejected', 'Payment', $payment->id, ['reason' => $reason]);

        return $payment->fresh();
    }

}
