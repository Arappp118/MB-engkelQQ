<?php

namespace Tests\Feature\Api;

use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSettingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_dapat_melihat_instruksi_pembayaran_via_api(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/payment-settings');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'cash' => ['name', 'is_active', 'instructions'],
                'transfer' => ['name', 'is_active', 'bank', 'instructions'],
                'qris' => ['name', 'is_active', 'instructions'],
            ],
        ]);
    }

    public function test_admin_dapat_update_payment_settings_via_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/payment-settings', [
                'bank_name' => 'Bank BNI',
                'bank_account_name' => 'Bengkel MotoCare BNI',
                'bank_account_number' => '0987654321',
                'bank_is_active' => true,
                'qris_merchant_name' => 'MotoCare Official BNI',
                'qris_is_active' => true,
            ]);

        $response->assertOk();
        $this->assertEquals('Bank BNI', PaymentSetting::get('bank_name'));
        $this->assertEquals('Bengkel MotoCare BNI', PaymentSetting::get('bank_account_name'));
        $this->assertEquals('0987654321', PaymentSetting::get('bank_account_number'));
    }

    public function test_non_admin_tidak_dapat_update_payment_settings_via_api(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/payment-settings', [
                'bank_name' => 'Bank Hacker',
            ]);

        $response->assertStatus(403);
    }
}
