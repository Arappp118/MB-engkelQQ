<?php

namespace App\Http\Requests\Payment;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Ownership dicek di sini: customer hanya boleh bayar service order
     * miliknya sendiri (via booking->customer_id). Admin boleh untuk semua.
     * amount TIDAK PERNAH divalidasi/diterima dari request — lihat rules().
     */
    public function authorize(): bool
    {
        $order = $this->route('service_order');

        if (!$order || !$this->user()->can('create', Payment::class)) {
            return false;
        }

        if ($this->user()->isAdmin()) {
            return true;
        }

        return $order->booking->customer_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'in:cash,transfer,qris'],
            // proof wajib untuk transfer/qris, tidak untuk cash
            'proof' => ['required_if:payment_method,transfer,qris', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof.required_if' => 'Bukti pembayaran wajib diunggah untuk transfer/QRIS.',
            'proof.mimes'        => 'File harus berformat JPG, PNG, atau PDF.',
            'proof.max'          => 'Ukuran file maksimal 2MB.',
        ];
    }
}
