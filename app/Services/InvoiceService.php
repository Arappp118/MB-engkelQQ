<?php

namespace App\Services;

use App\Models\ServiceOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * BR-013: Invoice tidak boleh dibuat dari data frontend yang dipercaya langsung.
     * BR-009: Invoice menggunakan snapshot harga transaksi.
     */
    public function getData(ServiceOrder $order): array
    {
        $order->load(['booking.customer', 'booking.vehicle', 'mechanic', 'items.serviceItem', 'payment']);

        return [
            'invoice_number' => $order->generateInvoiceNumber(),
            'nomor_booking'  => $order->booking->nomor_booking,
            'tanggal'        => now()->format('d/m/Y'),
            'customer'       => $order->booking->customer,
            'vehicle'        => $order->booking->vehicle,
            'mechanic'       => $order->mechanic,
            'items'          => $order->items,
            'subtotal'       => $order->subtotal,
            'delivery_fee'   => $order->delivery_fee,
            'grand_total'    => $order->grand_total,
            'payment'        => $order->payment,
        ];
    }

    public function generatePdf(ServiceOrder $order): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getData($order);
        return Pdf::loadView('invoice.pdf', $data)->setPaper('a4', 'portrait');
    }
}
