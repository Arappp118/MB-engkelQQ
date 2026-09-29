<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'nomor_polisi'  => $this->nomor_polisi,
            'merk'          => $this->merk,
            'model'         => $this->model,
            'tahun'         => $this->tahun,
            'tipe_mesin'    => $this->tipe_mesin,
            'transmisi'     => $this->transmisi,
            'warna'         => $this->warna,
            'nomor_rangka'  => $this->nomor_rangka,
            'catatan'       => $this->catatan,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
