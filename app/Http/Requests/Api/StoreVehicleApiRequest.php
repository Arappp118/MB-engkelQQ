<?php

namespace App\Http\Requests\Api;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Vehicle::class);
    }

    /**
     * user_id TIDAK ADA di rules ini secara sengaja. Meskipun client
     * mengirim 'user_id' di body, field itu tidak pernah masuk
     * validated() dan tidak pernah dipakai — VehicleService::create()
     * selalu mengambil owner dari $request->user() (lihat Controller).
     */
    public function rules(): array
    {
        return [
            'nomor_polisi'  => ['required', 'string', 'max:20', 'unique:vehicles,nomor_polisi'],
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
