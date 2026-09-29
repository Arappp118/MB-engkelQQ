<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Membungkus array dari InvoiceService::getData() — BUKAN model Eloquent,
 * jadi diakses sebagai array ($this->resource['key']), bukan magic property.
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'invoice_number' => $data['invoice_number'],
            'nomor_booking'  => $data['nomor_booking'],
            'tanggal'        => $data['tanggal'],
            'customer' => [
                'name'  => $data['customer']->name,
                'email' => $data['customer']->email,
            ],
            'vehicle' => [
                'nomor_polisi' => $data['vehicle']->nomor_polisi,
                'merk'         => $data['vehicle']->merk,
                'model'        => $data['vehicle']->model,
            ],
            'items' => collect($data['items'])->map(fn ($item) => [
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
        ];
    }
}
