<?php

namespace App\Http\Requests\Booking;

use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Booking::class);
    }

    public function rules(): array
    {
        return [
            'vehicle_id'             => ['required', 'integer', 'exists:vehicles,id'],
            'tanggal'                => ['required', 'date', 'after_or_equal:today'],
            'waktu'                  => ['required', 'date_format:H:i'],
            'keluhan'                => ['required', 'string', 'max:1000'],
            'diagnosis_customer'     => ['nullable', 'string', 'max:1000'],
            'jenis_layanan'          => ['required', 'in:medical_checkup,service_rutin,perbaikan'],
            'pickup_requested'       => ['boolean'],
            'alamat_pickup'          => ['required_if:pickup_requested,true', 'nullable', 'string', 'max:500'],
            'estimated_distance_km'  => ['required_if:pickup_requested,true', 'nullable', 'numeric', 'min:0'],
            'catatan'                => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->user()->isAdmin()) {
                return;
            }

            $vehicle = Vehicle::find($this->input('vehicle_id'));

            if ($vehicle && $vehicle->user_id !== $this->user()->id) {
                $validator->errors()->add('vehicle_id', 'Kendaraan tidak ditemukan atau bukan milik Anda.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'vehicle_id.exists'      => 'Kendaraan tidak ditemukan.',
            'tanggal.after_or_equal' => 'Tanggal booking tidak boleh di masa lalu.',
            'waktu.date_format'      => 'Format waktu harus HH:MM.',
        ];
    }
}
