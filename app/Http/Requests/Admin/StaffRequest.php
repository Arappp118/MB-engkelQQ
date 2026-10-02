<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $isCreate = is_null($userId);
        $forMechanic = str_contains($this->route()->getName() ?? '', 'mechanic');

        return [
            'name'           => ['required', 'string', 'max:255'],
            'email'          => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                $isCreate
                    ? 'unique:users,email'
                    : "unique:users,email,{$userId}",
            ],
            'password'       => $isCreate
                ? ['required', 'confirmed', Password::defaults()]
                : ['nullable', 'confirmed', Password::defaults()],
            'phone'          => ['nullable', 'string', 'max:20'],
            'address'        => ['nullable', 'string', 'max:1000'],
            'specialization' => $forMechanic
                ? ['required', 'in:mekanik_2_tak,mekanik_4_tak,mekanik_kelistrikan']
                : ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Nama wajib diisi.',
            'email.required'          => 'Email wajib diisi.',
            'email.unique'            => 'Email sudah digunakan oleh akun lain.',
            'password.required'       => 'Password wajib diisi.',
            'password.confirmed'      => 'Konfirmasi password tidak cocok.',
            'specialization.required' => 'Spesialisasi wajib dipilih.',
            'specialization.in'       => 'Spesialisasi tidak valid.',
        ];
    }
}
