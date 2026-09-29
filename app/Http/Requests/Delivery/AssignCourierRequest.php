<?php

namespace App\Http\Requests\Delivery;

use App\Models\DeliveryTask;
use Illuminate\Foundation\Http\FormRequest;

class AssignCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign', DeliveryTask::class);
    }

    public function rules(): array
    {
        return [
            'courier_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $courier = \App\Models\User::find($this->input('courier_id'));
            if ($courier && (!$courier->isCourier() || !$courier->is_active)) {
                $validator->errors()->add('courier_id', 'User yang dipilih bukan kurir aktif.');
            }
        });
    }
}
