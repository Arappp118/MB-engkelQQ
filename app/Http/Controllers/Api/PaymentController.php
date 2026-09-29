<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Payment\RejectPaymentRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    /**
     * Reuse StorePaymentRequest sama dengan Web — amount SELALU dari
     * $order->grand_total (server), ownership sudah dicek di authorize().
     */
    public function store(StorePaymentRequest $request, ServiceOrder $serviceOrder): JsonResponse
    {
        $payment = $this->paymentService->create($serviceOrder, $request->validated(), $request->file('proof'));

        return $this->success('Pembayaran berhasil diajukan', new PaymentResource($payment), 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        return $this->success('Detail pembayaran', new PaymentResource($payment));
    }

    public function verify(Payment $payment): JsonResponse
    {
        $this->authorize('verify', Payment::class);

        try {
            $updated = $this->paymentService->verify($payment, auth()->id());
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success('Pembayaran berhasil diverifikasi', new PaymentResource($updated));
    }

    public function reject(RejectPaymentRequest $request, Payment $payment): JsonResponse
    {
        try {
            $updated = $this->paymentService->reject($payment, auth()->id(), $request->validated('reason'));
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success('Pembayaran ditolak', new PaymentResource($updated));
    }
}
