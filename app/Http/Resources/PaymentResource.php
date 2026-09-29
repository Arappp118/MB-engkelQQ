<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * proof_path TIDAK diekspos (internal storage path) — hanya
     * boolean apakah bukti sudah diunggah.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'service_order_id'  => $this->service_order_id,
            'payment_method'    => $this->payment_method,
            'amount'            => $this->amount,
            'has_proof'         => !is_null($this->proof_path),
            'paid_at'           => $this->paid_at,
            'status'            => $this->status,
            'verified_by'       => $this->verified_by,
            'verified_at'       => $this->verified_at,
            'notes'             => $this->notes,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
