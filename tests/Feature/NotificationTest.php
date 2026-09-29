<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_dibuat_untuk_user_yang_benar(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $service = app(\App\Services\NotificationService::class);

        $service->notify($customer, 'Judul', 'Pesan', 'info');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'title' => 'Judul',
        ]);
    }

    public function test_user_lain_tidak_menerima_notification_yang_bukan_miliknya(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customer2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $notification = AppNotification::create([
            'user_id' => $customer1->id, 'title' => 'Punya Customer 1', 'message' => 'x', 'type' => 'info',
        ]);

        $response = $this->actingAs($customer2)->patch("/notifications/{$notification->id}/read");

        $response->assertForbidden();
    }

    public function test_notification_dapat_dibaca_lewat_index(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        AppNotification::create(['user_id' => $customer->id, 'title' => 'Test', 'message' => 'x', 'type' => 'info']);

        $response = $this->actingAs($customer)->get('/notifications');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Test']);
    }

    public function test_mark_as_read_bekerja(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $notification = AppNotification::create([
            'user_id' => $customer->id, 'title' => 'Test', 'message' => 'x', 'type' => 'info',
        ]);

        $response = $this->actingAs($customer)->patch("/notifications/{$notification->id}/read");

        $response->assertRedirect();
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_index_hanya_menampilkan_notifikasi_milik_sendiri(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customer2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        AppNotification::create(['user_id' => $customer1->id, 'title' => 'Punya C1', 'message' => 'x', 'type' => 'info']);
        AppNotification::create(['user_id' => $customer2->id, 'title' => 'Punya C2', 'message' => 'x', 'type' => 'info']);

        $response = $this->actingAs($customer1)->get('/notifications');

        $response->assertJsonFragment(['title' => 'Punya C1']);
        $response->assertJsonMissing(['title' => 'Punya C2']);
    }

    public function test_booking_dibuat_menghasilkan_notification_customer(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B999NT',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);

        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '10:00',
            'keluhan' => 'Rem bunyi',
            'jenis_layanan' => 'perbaikan',
            'pickup_requested' => false,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'title' => 'Booking Dibuat',
        ]);
    }

    public function test_service_order_dimulai_menghasilkan_notification_customer(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B888NT',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'Test', 'jenis_layanan' => 'perbaikan', 'status' => 'assigned',
        ]);
        $order = ServiceOrder::create([
            'booking_id' => $booking->id, 'mechanic_id' => $mechanic->id, 'status' => 'pending',
        ]);

        $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/start");

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'title' => 'Kendaraan Mulai Diservis',
        ]);
    }

    public function test_unauthorized_read_menghasilkan_403(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $randomUser = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $notification = AppNotification::create([
            'user_id' => $customer->id, 'title' => 'Private', 'message' => 'x', 'type' => 'info',
        ]);

        $response = $this->actingAs($randomUser)->patch("/notifications/{$notification->id}/read");

        $response->assertForbidden();
    }
}
