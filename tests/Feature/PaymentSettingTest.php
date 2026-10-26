<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_halaman_pengaturan_pembayaran(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/payment-settings');

        $response->assertOk();
        $response->assertSee('Konfigurasi Rekening');
    }

    public function test_non_admin_tidak_dapat_mengakses_halaman_pengaturan_pembayaran(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);

        $this->actingAs($customer)->get('/admin/payment-settings')->assertForbidden();
        $this->actingAs($mechanic)->get('/admin/payment-settings')->assertForbidden();
        $this->actingAs($courier)->get('/admin/payment-settings')->assertForbidden();
    }

    public function test_admin_dapat_memperbarui_rekening_bank_dan_qris(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $qrisImage = UploadedFile::fake()->image('qris.png', 300, 300);

        $response = $this->actingAs($admin)->patch('/admin/payment-settings', [
            'bank_name' => 'Bank Mandiri',
            'bank_account_name' => 'Bengkel MotoCare Mandiri',
            'bank_account_number' => '1370012345678',
            'bank_is_active' => '1',
            'qris_merchant_name' => 'MotoCare Mandiri QRIS',
            'qris_is_active' => '1',
            'qris_image' => $qrisImage,
        ]);

        $response->assertRedirect();
        $this->assertEquals('Bank Mandiri', PaymentSetting::get('bank_name'));
        $this->assertEquals('Bengkel MotoCare Mandiri', PaymentSetting::get('bank_account_name'));
        $this->assertEquals('1370012345678', PaymentSetting::get('bank_account_number'));
        $this->assertTrue(filter_var(PaymentSetting::get('bank_is_active'), FILTER_VALIDATE_BOOLEAN));
        $this->assertEquals('MotoCare Mandiri QRIS', PaymentSetting::get('qris_merchant_name'));
        $this->assertNotNull(PaymentSetting::get('qris_image_path'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment_settings.updated',
            'entity_type' => 'PaymentSetting',
        ]);
    }
}
