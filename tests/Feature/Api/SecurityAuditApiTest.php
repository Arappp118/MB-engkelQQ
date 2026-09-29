<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\DeliveryTask;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuditApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_double_delivery_completion_ditolak(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B' . rand(1000, 9999) . 'SA',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022, 'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(), 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00', 'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'confirmed',
        ]);
        $task = DeliveryTask::create([
            'booking_id' => $booking->id, 'courier_id' => $courier->id, 'type' => 'pickup',
            'address' => 'x', 'distance_km' => 5, 'delivery_fee' => 10000, 'status' => 'in_progress',
        ]);
        $token = $courier->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/complete")->assertOk();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/complete");

        $response->assertStatus(422);
    }

    public function test_double_service_order_completion_ditolak(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B' . rand(1000, 9999) . 'SB',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022, 'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(), 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00', 'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'in_service',
        ]);
        $order = ServiceOrder::create(['booking_id' => $booking->id, 'mechanic_id' => $mechanic->id, 'status' => 'completed']);
        $token = $mechanic->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/complete");

        $response->assertStatus(422);
    }

    public function test_double_booking_confirm_ditolak(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B' . rand(1000, 9999) . 'SC',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022, 'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(), 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00', 'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'pending',
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/bookings/{$booking->id}/confirm")->assertOk();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/bookings/{$booking->id}/confirm");

        $response->assertStatus(422);
    }

    public function test_404_untuk_resource_tidak_ditemukan_tanpa_leakage(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/vehicles/999999');

        $response->assertStatus(404);
        $response->assertJsonStructure(['success', 'message']);
        $response->assertJsonMissingPath('errors.exception');
        $content = $response->getContent();
        $this->assertStringNotContainsString('.php', $content);
        $this->assertStringNotContainsString('Stack trace', $content);
    }

    public function test_405_method_not_allowed_pada_health(): void
    {
        $response = $this->putJson('/api/v1/health', []);

        $response->assertStatus(405);
        $response->assertJsonStructure(['success', 'message']);
    }
}
