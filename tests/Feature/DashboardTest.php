<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_diarahkan_ke_dashboard_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect('/admin/dashboard');
        $this->followingRedirects()->actingAs($admin)->get('/dashboard')->assertOk();
    }

    public function test_mechanic_diarahkan_ke_dashboard_mechanic(): void
    {
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);

        $response = $this->actingAs($mechanic)->get('/dashboard');

        $response->assertRedirect('/mekanik/dashboard');
        $this->followingRedirects()->actingAs($mechanic)->get('/dashboard')->assertOk();
    }

    public function test_courier_diarahkan_ke_dashboard_courier(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);

        $response = $this->actingAs($courier)->get('/dashboard');

        $response->assertRedirect('/kurir/dashboard');
        $this->followingRedirects()->actingAs($courier)->get('/dashboard')->assertOk();
    }

    public function test_customer_langsung_lihat_dashboard_sendiri(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $response = $this->actingAs($customer)->get('/dashboard');

        $response->assertOk();
    }

    public function test_customer_tidak_dapat_membuka_dashboard_admin(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_mechanic_tidak_dapat_membuka_dashboard_admin(): void
    {
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);

        $response = $this->actingAs($mechanic)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_courier_tidak_dapat_membuka_dashboard_admin(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);

        $response = $this->actingAs($courier)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_dapat_membuka_dashboard_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
    }

    public function test_data_customer_terisolasi(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customer2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        Vehicle::create([
            'user_id' => $customer1->id, 'nomor_polisi' => 'B1DASH',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);

        $response = $this->actingAs($customer2)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('vehicles_count', 0); // customer2 tidak punya kendaraan
    }

    public function test_data_mechanic_terisolasi(): void
    {
        $mechanic1 = User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']);
        $mechanic2 = User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B2DASH',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'Test', 'jenis_layanan' => 'perbaikan', 'status' => 'assigned',
        ]);
        \App\Models\ServiceOrder::create([
            'booking_id' => $booking->id, 'mechanic_id' => $mechanic1->id, 'status' => 'pending',
        ]);

        $response = $this->actingAs($mechanic2)->get('/mekanik/dashboard');

        $response->assertOk();
        $response->assertViewHas('assigned_total', 0); // mechanic2 tidak punya order ini
    }

    public function test_data_courier_terisolasi(): void
    {
        $courier1 = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $courier2 = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B3DASH',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'Test', 'jenis_layanan' => 'perbaikan', 'status' => 'confirmed',
        ]);
        \App\Models\DeliveryTask::create([
            'booking_id' => $booking->id, 'courier_id' => $courier1->id, 'type' => 'pickup',
            'address' => 'Jl. Test', 'distance_km' => 5, 'delivery_fee' => 10000, 'status' => 'assigned',
        ]);

        $response = $this->actingAs($courier2)->get('/kurir/dashboard');

        $response->assertOk();
        $response->assertViewHas('active_total', 0); // courier2 tidak punya task ini
    }

    public function test_admin_dashboard_menampilkan_overview_sistem(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('total_customers');
    }
}
