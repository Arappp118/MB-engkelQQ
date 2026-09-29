<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_menghasilkan_401(): void
    {
        $this->getJson('/api/v1/dashboard')->assertStatus(401);
    }

    public function test_customer_mendapatkan_dashboard_customer(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['vehicles_count', 'active_bookings']]);
    }

    public function test_mechanic_mendapatkan_dashboard_mechanic(): void
    {
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $token = $mechanic->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['assigned_total', 'pending', 'in_progress']]);
    }

    public function test_courier_mendapatkan_dashboard_courier(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $token = $courier->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['pickup_total', 'delivery_total']]);
    }

    public function test_admin_mendapatkan_dashboard_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['total_customers', 'total_vehicles']]);
    }

    public function test_data_customer_terisolasi(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customer2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        Vehicle::create([
            'user_id' => $customer1->id, 'nomor_polisi' => 'B1DASHAPI',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $token = $customer2->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/dashboard');

        $response->assertJsonPath('data.vehicles_count', 0);
    }

    public function test_tidak_ada_cross_role_leakage_customer_tidak_dapat_data_admin(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/dashboard');

        $response->assertJsonMissingPath('data.total_customers');
    }
}
