<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('vehicle'));
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle');

        return [
            'nomor_polisi'  => ['required', 'string', 'max:20', 'unique:vehicles,nomor_polisi,' . $vehicle->id],
            'merk'          => ['required', 'string', 'max:100'],
            'model'         => ['required', 'string', 'max:100'],
            'tahun'         => ['required', 'integer', 'min:1980', 'max:' . (date('Y') + 1)],
            'tipe_mesin'    => ['required', 'in:2_tak,4_tak,listrik'],
            'transmisi'     => ['required', 'in:manual,matic'],
            'warna'         => ['nullable', 'string', 'max:50'],
            'nomor_rangka'  => ['nullable', 'string', 'max:50'],
            'catatan'       => ['nullable', 'string', 'max:500'],
        ];
    }
}
