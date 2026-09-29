<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'category'     => $this->category,
            'name'         => $this->name,
            'description'  => $this->description,
            'price'        => $this->price,
            'unit'         => $this->unit,
            'stock'        => $this->stock,
            'is_sparepart' => $this->is_sparepart,
            'is_active'    => $this->is_active,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
