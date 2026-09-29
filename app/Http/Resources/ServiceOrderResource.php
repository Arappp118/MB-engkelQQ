<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'booking_id'         => $this->booking_id,
            'mechanic_id'        => $this->mechanic_id,
            'diagnosis_mechanic' => $this->diagnosis_mechanic,
            'notes'              => $this->notes,
            'status'             => $this->status,
            'started_at'         => $this->started_at,
            'completed_at'       => $this->completed_at,
            'subtotal'           => $this->subtotal,
            'delivery_fee'       => $this->delivery_fee,
            'grand_total'        => $this->grand_total,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'name'     => $item->item_name_snapshot,
                'price'    => $item->price_snapshot,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
            ])),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
