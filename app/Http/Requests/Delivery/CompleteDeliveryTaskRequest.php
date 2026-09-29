<?php

namespace App\Http\Requests\Delivery;

use Illuminate\Foundation\Http\FormRequest;

class CompleteDeliveryTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('complete', $this->route('delivery_task'));
    }

    /**
     * delivery_fee / distance_km TIDAK divalidasi di sini secara sengaja —
     * fee sudah dihitung & disimpan saat task dibuat, tidak boleh diubah
     * lewat aksi penyelesaian task.
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
