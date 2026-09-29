<?php

namespace App\Http\Requests\ServiceOrder;

use Illuminate\Foundation\Http\FormRequest;

class SaveDiagnosisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('service_order'));
    }

    public function rules(): array
    {
        return [
            'diagnosis_mechanic' => ['required', 'string', 'max:2000'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'diagnosis_mechanic.required' => 'Diagnosis mekanik wajib diisi.',
        ];
    }
}
