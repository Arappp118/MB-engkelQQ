<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    /**
     * Invoice adalah representasi read-only dari ServiceOrder + relasinya.
     * Semua angka (harga, delivery fee, grand total) berasal dari data
     * snapshot yang sudah tersimpan — tidak ada perhitungan ulang dari
     * harga master saat ini, dan tidak ada input dari request/frontend.
     */
    public function show(ServiceOrder $serviceOrder): JsonResponse
    {
        $this->authorize('view', $serviceOrder);

        $data = $this->invoiceService->getData($serviceOrder);

        return response()->json([
            'invoice_number' => $data['invoice_number'],
            'nomor_booking'  => $data['nomor_booking'],
            'tanggal'        => $data['tanggal'],
            'customer'       => [
                'name'  => $data['customer']->name,
                'email' => $data['customer']->email,
            ],
            'vehicle' => [
                'nomor_polisi' => $data['vehicle']->nomor_polisi,
                'merk'         => $data['vehicle']->merk,
                'model'        => $data['vehicle']->model,
            ],
            'items' => $data['items']->map(fn ($item) => [
                'name'     => $item->item_name_snapshot,
                'price'    => $item->price_snapshot,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
            ]),
            'subtotal'       => $data['subtotal'],
            'delivery_fee'   => $data['delivery_fee'],
            'grand_total'    => $data['grand_total'],
            'payment_status' => $data['payment']?->status,
            'payment_method' => $data['payment']?->payment_method,
        ]);
    }
}
