<?php

namespace App\Http\Requests\Delivery;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliverySettingRequest extends FormRequest
{
    /**
     * Hanya admin yang boleh mengubah tarif ongkir resmi.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'price_per_km' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'price_per_km.min' => 'Tarif tidak boleh negatif.',
        ];
    }
}
