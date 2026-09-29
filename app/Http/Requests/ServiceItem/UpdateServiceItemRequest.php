<?php

namespace App\Http\Requests\ServiceItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('service_item'));
    }

    public function rules(): array
    {
        return [
            'category'     => ['required', 'in:2_tak,4_tak,kelistrikan,umum,sparepart'],
            'name'         => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'price'        => ['required', 'numeric', 'min:0'],
            'unit'         => ['nullable', 'string', 'max:50'],
            'is_sparepart' => ['required', 'boolean'],
            'stock'        => ['required_if:is_sparepart,true', 'nullable', 'integer', 'min:0'],
            'is_active'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.required' => 'Harga wajib diisi.',
            'price.min'      => 'Harga tidak boleh negatif.',
            'stock.required_if' => 'Stok wajib diisi untuk sparepart.',
        ];
    }
}
