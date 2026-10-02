<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Services\InvoiceService;
use Illuminate\View\View;

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
    public function show(ServiceOrder $serviceOrder): View
    {
        $this->authorize('view', $serviceOrder);

        $data = $this->invoiceService->getData($serviceOrder);

        return view('invoices.show', [
            'serviceOrder' => $serviceOrder,
            'invoiceData'  => $data
        ]);
    }
}
