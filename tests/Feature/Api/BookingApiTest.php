<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function customerWithVehicle(): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B' . rand(1000, 9999) . 'BK',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        return [$customer, $vehicle, $customer->createToken('t')->plainTextToken];
    }

    protected function payload(int $vehicleId, array $overrides = []): array
    {
        return array_merge([
            'vehicle_id' => $vehicleId,
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '10:00',
            'keluhan' => 'Rem bunyi',
            'jenis_layanan' => 'perbaikan',
            'pickup_requested' => false,
        ], $overrides);
    }

    public function test_unauthenticated_menghasilkan_401(): void
    {
        $this->getJson('/api/v1/bookings')->assertStatus(401);
        $this->postJson('/api/v1/bookings', [])->assertStatus(401);
    }

    public function test_customer_dapat_membuat_dan_melihat_booking(): void
    {
        [, $vehicle, $token] = $this->customerWithVehicle();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bookings', $this->payload($vehicle->id));

        $response->assertStatus(201);
        $bookingId = $response->json('data.id');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/bookings/{$bookingId}")->assertOk();
    }

    public function test_customer_hanya_melihat_booking_miliknya_di_list(): void
    {
        [$customer1, $vehicle1, $token1] = $this->customerWithVehicle();
        [$customer2, $vehicle2] = $this->customerWithVehicle();

        Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer2->id, 'vehicle_id' => $vehicle2->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token1}")->getJson('/api/v1/bookings');

        $response->assertJsonCount(0, 'data');
    }

    public function test_idor_customer_tidak_bisa_lihat_booking_customer_lain(): void
    {
        [, $vehicle1, $token1] = $this->customerWithVehicle();
        [$customer2, $vehicle2] = $this->customerWithVehicle();

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer2->id, 'vehicle_id' => $vehicle2->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'pending',
        ]);

        $this->withHeader('Authorization', "Bearer {$token1}")
            ->getJson("/api/v1/bookings/{$booking->id}")->assertStatus(403);
    }

    public function test_vehicle_ownership_diverifikasi_saat_create(): void
    {
        [, $vehicle1, $token1] = $this->customerWithVehicle();
        [, $vehicle2] = $this->customerWithVehicle();

        $response = $this->withHeader('Authorization', "Bearer {$token1}")
            ->postJson('/api/v1/bookings', $this->payload($vehicle2->id));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('vehicle_id');
    }

    public function test_validation_gagal_menghasilkan_422(): void
    {
        [, $vehicle, $token] = $this->customerWithVehicle();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bookings', $this->payload($vehicle->id, ['keluhan' => '']));

        $response->assertStatus(422);
    }

    public function test_customer_dapat_cancel_booking_pending(): void
    {
        [$customer, $vehicle, $token] = $this->customerWithVehicle();
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/bookings/{$booking->id}/cancel", ['cancellation_reason' => 'batal']);

        $response->assertOk();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_status_guard_tidak_bisa_cancel_booking_completed(): void
    {
        [$customer, $vehicle, $token] = $this->customerWithVehicle();
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'completed',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/bookings/{$booking->id}/cancel", []);

        // Policy::cancel() menolak di level otorisasi untuk status selain
        // pending/confirmed (403), sebelum sempat sampai ke guard RuntimeException
        // di Service (422). Ini behavior existing yang sama sejak Phase 2.
        $response->assertStatus(403);
    }

    public function test_customer_tidak_dapat_cancel_booking_customer_lain(): void
    {
        [, $vehicle1, $token1] = $this->customerWithVehicle();
        [$customer2, $vehicle2] = $this->customerWithVehicle();
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer2->id, 'vehicle_id' => $vehicle2->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'pending',
        ]);

        $this->withHeader('Authorization', "Bearer {$token1}")
            ->postJson("/api/v1/bookings/{$booking->id}/cancel", [])
            ->assertStatus(403);
    }

    public function test_customer_tidak_dapat_confirm_booking(): void
    {
        [$customer, $vehicle, $token] = $this->customerWithVehicle();
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'pending',
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/bookings/{$booking->id}/confirm", [])
            ->assertStatus(403);
    }

    public function test_admin_dapat_confirm_booking(): void
    {
        [$customer, $vehicle] = $this->customerWithVehicle();
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'pending',
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $adminToken = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/v1/bookings/{$booking->id}/confirm", [])
            ->assertOk();
    }

    public function test_auditlog_tercatat_saat_booking_dibuat(): void
    {
        [, $vehicle, $token] = $this->customerWithVehicle();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bookings', $this->payload($vehicle->id));

        $this->assertDatabaseHas('audit_logs', ['action' => 'booking.created']);
    }

    public function test_notification_dikirim_saat_booking_dibuat(): void
    {
        [$customer, $vehicle, $token] = $this->customerWithVehicle();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bookings', $this->payload($vehicle->id));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'title' => 'Booking Dibuat',
        ]);
    }

    public function test_mechanic_assignment_terjadi_saat_confirm(): void
    {
        [$customer, $vehicle, $token] = $this->customerWithVehicle();
        User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'pending',
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $adminToken = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/v1/bookings/{$booking->id}/confirm", []);

        $this->assertDatabaseHas('service_orders', ['booking_id' => $booking->id]);
    }

    public function test_response_tidak_membocorkan_data_sensitif(): void
    {
        [, $vehicle, $token] = $this->customerWithVehicle();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bookings', $this->payload($vehicle->id));

        $response->assertJsonMissingPath('data.customer.password');
    }
}
