<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\UpdatePaymentSettingRequest;
use App\Models\AuditLog;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    public function show(): View
    {
        $settings = [
            'bank_name' => PaymentSetting::get('bank_name', 'Bank Central Asia (BCA)'),
            'bank_account_name' => PaymentSetting::get('bank_account_name', 'Bengkel MotoCare'),
            'bank_account_number' => PaymentSetting::get('bank_account_number', '8830123456'),
            'bank_is_active' => filter_var(PaymentSetting::get('bank_is_active', '1'), FILTER_VALIDATE_BOOLEAN),
            'qris_merchant_name' => PaymentSetting::get('qris_merchant_name', 'MotoCare Official'),
            'qris_image_path' => PaymentSetting::get('qris_image_path', null),
            'qris_is_active' => filter_var(PaymentSetting::get('qris_is_active', '1'), FILTER_VALIDATE_BOOLEAN),
        ];

        return view('admin.payment-settings.index', compact('settings'));
    }

    public function update(UpdatePaymentSettingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        PaymentSetting::set('bank_name', $validated['bank_name'] ?? '');
        PaymentSetting::set('bank_account_name', $validated['bank_account_name'] ?? '');
        PaymentSetting::set('bank_account_number', $validated['bank_account_number'] ?? '');
        PaymentSetting::set('bank_is_active', ! empty($validated['bank_is_active']) ? '1' : '0');

        PaymentSetting::set('qris_merchant_name', $validated['qris_merchant_name'] ?? '');
        PaymentSetting::set('qris_is_active', ! empty($validated['qris_is_active']) ? '1' : '0');

        if ($request->hasFile('qris_image')) {
            $path = $request->file('qris_image')->store('payment-settings', 'public');
            PaymentSetting::set('qris_image_path', $path);
        }

        AuditLog::record('payment_settings.updated', 'PaymentSetting', null, [
            'updated_by' => auth()->id(),
            'bank_name' => $validated['bank_name'] ?? '',
            'qris_name' => $validated['qris_merchant_name'] ?? '',
        ]);

        return back()->with('success', 'Konfigurasi pembayaran berhasil diperbarui.');
    }
}
