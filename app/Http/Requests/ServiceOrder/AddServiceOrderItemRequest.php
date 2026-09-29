<?php

namespace App\Http\Requests\ServiceOrder;

use Illuminate\Foundation\Http\FormRequest;

class AddServiceOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('addItem', $this->route('service_order'));
    }

    /**
     * HANYA service_item_id dan quantity yang diterima.
     * price_snapshot / subtotal / unit_price TIDAK divalidasi di sini
     * secara sengaja — jika dikirim, akan diabaikan sepenuhnya oleh
     * Controller (tidak masuk validated(), tidak diteruskan ke Service).
     */
    public function rules(): array
    {
        return [
            'service_item_id' => ['required', 'integer', 'exists:service_items,id,is_active,1'],
            'quantity'        => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'Jumlah minimal 1.',
            'service_item_id.exists' => 'Item tidak ditemukan.',
        ];
    }
}
