<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value, ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description ?? static::where('key', $key)->value('description'),
            ]
        );
    }

    /**
     * Rekening bank bengkel yang aktif.
     */
    public static function getActiveBank(): ?array
    {
        $isActive = filter_var(static::get('bank_is_active', '1'), FILTER_VALIDATE_BOOLEAN);
        if (! $isActive) {
            return null;
        }

        $bankName = static::get('bank_name', 'Bank Central Asia (BCA)');
        $accName = static::get('bank_account_name', 'Bengkel MotoCare');
        $accNum = static::get('bank_account_number', '8830123456');

        return [
            'bank_name' => $bankName,
            'account_name' => $accName,
            'account_number' => $accNum,
            'is_active' => true,
        ];
    }

    /**
     * Konfigurasi QRIS statis bengkel.
     */
    public static function getQrisConfig(): ?array
    {
        $isActive = filter_var(static::get('qris_is_active', '1'), FILTER_VALIDATE_BOOLEAN);
        if (! $isActive) {
            return null;
        }

        return [
            'merchant_name' => static::get('qris_merchant_name', 'MotoCare Official'),
            'image_path' => static::get('qris_image_path', null),
            'is_active' => true,
            'is_static' => true,
            'instructions' => 'Pindai QRIS dan masukkan nominal tagihan secara manual sesuai invoice.',
        ];
    }

    /**
     * Instruksi publik untuk pelanggan.
     */
    public static function getPaymentInstructions(): array
    {
        return [
            'cash' => [
                'name' => 'Tunai (Cash)',
                'is_active' => true,
                'instructions' => 'Pembayaran tunai dapat dilakukan langsung di kasir bengkel saat pengambilan kendaraan.',
            ],
            'transfer' => [
                'name' => 'Transfer Bank',
                'is_active' => filter_var(static::get('bank_is_active', '1'), FILTER_VALIDATE_BOOLEAN),
                'bank' => static::getActiveBank(),
                'instructions' => 'Transfer tepat sesuai nominal tagihan ke rekening di atas, lalu unggah bukti transfer yang sah (JPG, PNG, atau PDF).',
            ],
            'qris' => [
                'name' => 'QRIS',
                'is_active' => filter_var(static::get('qris_is_active', '1'), FILTER_VALIDATE_BOOLEAN),
                'qris' => static::getQrisConfig(),
                'instructions' => 'Pindai kode QRIS, input nominal sesuai total tagihan, selesaikan pembayaran, lalu unggah tangkapan layar bukti bayar.',
            ],
        ];
    }
}
