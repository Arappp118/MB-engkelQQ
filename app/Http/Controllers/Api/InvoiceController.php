<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\InvoiceResource;
use App\Models\ServiceOrder;
use App\Services\InvoiceService;
use App\Services\ServiceOrderService;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected ServiceOrderService $serviceOrderService
    ) {
    }

    /**
     * Ringkasan invoice (service order yang sudah completed), reuse
     * listFor() yang sama dengan Service Order (ownership sudah dijamin).
     */
    public function index(): JsonResponse
    {
        $orders = $this->serviceOrderService->listFor(auth()->user())
            ->where('status', 'completed');

        $summary = $orders->map(fn (ServiceOrder $order) => [
            'invoice_number' => $order->generateInvoiceNumber(),
            'service_order_id' => $order->id,
            'grand_total' => $order->grand_total,
        ])->values();

        return $this->success('Daftar invoice', $summary);
    }

    /**
     * Read-only. Semua angka dari snapshot historis (InvoiceService::getData()
     * existing, tidak dihitung ulang dari harga master).
     */
    public function show(ServiceOrder $serviceOrder): JsonResponse
    {
        $this->authorize('view', $serviceOrder);

        return $this->success('Detail invoice', new InvoiceResource($this->invoiceService->getData($serviceOrder)));
    }
}
