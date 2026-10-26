<?php

namespace App\Http\Requests\Payment;

use App\Models\Payment;
use App\Models\ServiceOrder;
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

        if (! $order || ! $this->user()->can('create', Payment::class)) {
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
            'proof.mimes' => 'File harus berformat JPG, PNG, atau PDF.',
            'proof.max' => 'Ukuran file maksimal 2MB.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $order = $this->route('service_order');
            if (! $order instanceof ServiceOrder) {
                return;
            }

            if ($order->status !== 'completed') {
                $validator->errors()->add('service_order', 'Pembayaran hanya dapat diajukan setelah servis selesai.');
            }

            if ($order->grand_total <= 0) {
                $validator->errors()->add('service_order', 'Total tagihan servis tidak valid untuk pembayaran.');
            }

            $hasActivePayment = $order->payments()
                ->whereIn('status', ['waiting_verification', 'verified', 'paid'])
                ->exists();

            if ($hasActivePayment) {
                $validator->errors()->add('service_order', 'Pembayaran untuk order ini sudah diajukan atau sudah lunas.');
            }
        });
    }
}
