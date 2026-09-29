<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCustomerWithVehicle(): array
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'XYZ',
            'merk' => 'Honda',
            'model' => 'Vario',
            'tahun' => 2022,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'matic',
        ]);

        return [$customer, $vehicle];
    }

    public function test_customer_dapat_membuat_booking_dengan_kendaraan_miliknya(): void
    {
        [$customer, $vehicle] = $this->makeCustomerWithVehicle();

        $response = $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '10:00',
            'keluhan' => 'Rem depan bunyi',
            'jenis_layanan' => 'perbaikan',
            'pickup_requested' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);
    }

    public function test_customer_dapat_melihat_booking_miliknya(): void
    {
        [$customer, $vehicle] = $this->makeCustomerWithVehicle();

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(),
            'waktu' => '10:00',
            'keluhan' => 'Test',
            'jenis_layanan' => 'perbaikan',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->get("/bookings/{$booking->id}");

        // View bookings.show sudah tersedia. Validasi: customer mendapat 200
        // dan halaman menampilkan data booking miliknya.
        $response->assertStatus(200);
        $response->assertSee($booking->nomor_booking);
        $response->assertSee('Test'); // keluhan
    }

    public function test_customer_tidak_dapat_melihat_booking_milik_customer_lain(): void
    {
        [$customer1, $vehicle1] = $this->makeCustomerWithVehicle();
        [$customer2, $vehicle2] = $this->makeCustomerWithVehicle();

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer1->id,
            'vehicle_id' => $vehicle1->id,
            'tanggal' => now()->addDay(),
            'waktu' => '10:00',
            'keluhan' => 'Test',
            'jenis_layanan' => 'perbaikan',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer2)->get("/bookings/{$booking->id}");

        $response->assertForbidden();
    }

    public function test_customer_tidak_dapat_booking_menggunakan_kendaraan_customer_lain(): void
    {
        [$customer1, $vehicle1] = $this->makeCustomerWithVehicle();
        [$customer2, $vehicle2] = $this->makeCustomerWithVehicle();

        $response = $this->actingAs($customer2)->post('/bookings', [
            'vehicle_id' => $vehicle1->id, // kendaraan milik customer1
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '10:00',
            'keluhan' => 'Rem depan bunyi',
            'jenis_layanan' => 'perbaikan',
            'pickup_requested' => false,
        ]);

        $response->assertSessionHasErrors('vehicle_id');
        $this->assertDatabaseMissing('bookings', [
            'vehicle_id' => $vehicle1->id,
            'customer_id' => $customer2->id,
        ]);
    }

    public function test_customer_dapat_membatalkan_booking_status_pending(): void
    {
        [$customer, $vehicle] = $this->makeCustomerWithVehicle();

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(),
            'waktu' => '10:00',
            'keluhan' => 'Test',
            'jenis_layanan' => 'perbaikan',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->patch("/bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Berubah pikiran',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_booking_dengan_data_invalid_ditolak(): void
    {
        [$customer, $vehicle] = $this->makeCustomerWithVehicle();

        $response = $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->subDay()->toDateString(), // tanggal masa lalu
            'waktu' => '10:00',
            'keluhan' => '',
            'jenis_layanan' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors(['tanggal', 'keluhan', 'jenis_layanan']);
    }

    public function test_auditlog_tercatat_saat_booking_dibuat(): void
    {
        [$customer, $vehicle] = $this->makeCustomerWithVehicle();

        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '10:00',
            'keluhan' => 'Rem depan bunyi',
            'jenis_layanan' => 'perbaikan',
            'pickup_requested' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'booking.created',
        ]);
    }
}
