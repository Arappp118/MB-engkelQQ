<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'nomor_booking'          => $this->nomor_booking,
            'customer_id'            => $this->customer_id,
            'vehicle_id'             => $this->vehicle_id,
            'tanggal'                => $this->tanggal,
            'waktu'                  => $this->waktu,
            'keluhan'                => $this->keluhan,
            'diagnosis_customer'     => $this->diagnosis_customer,
            'jenis_layanan'          => $this->jenis_layanan,
            'pickup_requested'       => $this->pickup_requested,
            'alamat_pickup'          => $this->alamat_pickup,
            'estimated_distance_km'  => $this->estimated_distance_km,
            'status'                 => $this->status,
            'status_label'           => $this->status_label,
            'catatan'                => $this->catatan,
            'cancellation_reason'    => $this->cancellation_reason,
            'vehicle'                => new VehicleResource($this->whenLoaded('vehicle')),
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
