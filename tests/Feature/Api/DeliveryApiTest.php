<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\DeliverySetting;
use App\Models\DeliveryTask;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeTask(float $distanceKm = 5, float $fee = 10000): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B' . rand(1000, 9999) . 'DA',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'confirmed',
        ]);
        $task = DeliveryTask::create([
            'booking_id' => $booking->id, 'courier_id' => null, 'type' => 'pickup',
            'address' => 'Jl. Test', 'distance_km' => $distanceKm, 'delivery_fee' => $fee, 'status' => 'pending',
        ]);
        return [$customer, $task];
    }

    public function test_courier_hanya_melihat_task_miliknya(): void
    {
        [, $task] = $this->makeTask();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $task->update(['courier_id' => $courier->id]);
        $token = $courier->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/delivery-tasks/{$task->id}")->assertOk();
    }

    public function test_courier_lain_tidak_dapat_akses(): void
    {
        [, $task] = $this->makeTask();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $courierLain = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $task->update(['courier_id' => $courier->id]);
        $token = $courierLain->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/delivery-tasks/{$task->id}")->assertStatus(403);
    }

    public function test_admin_dapat_assign_courier(): void
    {
        [, $task] = $this->makeTask();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/assign", ['courier_id' => $courier->id])
            ->assertOk();

        $this->assertDatabaseHas('delivery_tasks', ['id' => $task->id, 'courier_id' => $courier->id]);
    }

    public function test_non_admin_tidak_dapat_assign(): void
    {
        [, $task] = $this->makeTask();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $token = $courier->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/assign", ['courier_id' => $courier->id])
            ->assertStatus(403);
    }

    public function test_fee_tidak_dapat_dimanipulasi_saat_assign(): void
    {
        [, $task] = $this->makeTask(5, 10000);
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/assign", [
                'courier_id' => $courier->id,
                'delivery_fee' => 999999,
            ]);

        $this->assertDatabaseHas('delivery_tasks', ['id' => $task->id, 'delivery_fee' => 10000]);
    }

    public function test_courier_tidak_dapat_ubah_tarif(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $token = $courier->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/delivery-settings', ['price_per_km' => 1])
            ->assertStatus(403);
    }

    public function test_admin_dapat_ubah_tarif(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/delivery-settings', ['price_per_km' => 5000])
            ->assertOk();

        $this->assertEquals(5000, (float) DeliverySetting::get('price_per_km'));
    }

    public function test_fee_snapshot_stabil_setelah_tarif_berubah(): void
    {
        [, $task] = $this->makeTask(5, 10000);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/delivery-settings', ['price_per_km' => 99999]);

        $task->refresh();
        $this->assertEquals(10000, $task->delivery_fee);
    }

    public function test_pickup_completion_berhasil(): void
    {
        [, $task] = $this->makeTask();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $task->update([
            'courier_id' => $courier->id,
            'status' => 'assigned',
        ]);
        $token = $courier->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")->postJson("/api/v1/delivery-tasks/{$task->id}/start")->assertOk();
        $this->withHeader('Authorization', "Bearer {$token}")->postJson("/api/v1/delivery-tasks/{$task->id}/complete")->assertOk();

        $this->assertDatabaseHas('delivery_tasks', ['id' => $task->id, 'status' => 'completed']);
        // Tanpa mekanik tersedia, status booking otomatis menunggu servis
        $this->assertDatabaseHas('bookings', ['id' => $task->booking_id, 'status' => 'waiting_service']);
    }

    public function test_pickup_completion_dengan_mekanik_otomatis_assign_dan_buat_service_order(): void
    {
        [, $task] = $this->makeTask();
        $mechanic = User::factory()->create([
            'role'           => 'mechanic',
            'is_active'      => true,
            'specialization' => 'mekanik_4_tak',
        ]);
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $task->update([
            'courier_id' => $courier->id,
            'status'     => 'assigned',
        ]);
        $token = $courier->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")->postJson("/api/v1/delivery-tasks/{$task->id}/start")->assertOk();
        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson("/api/v1/delivery-tasks/{$task->id}/complete");

        $response->assertOk();
        $this->assertDatabaseHas('delivery_tasks', ['id' => $task->id, 'status' => 'completed']);
        $this->assertDatabaseHas('bookings', ['id' => $task->booking_id, 'status' => 'assigned']);
        $this->assertDatabaseHas('service_orders', [
            'booking_id'  => $task->booking_id,
            'mechanic_id' => $mechanic->id,
            'status'      => 'pending',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'delivery.pickup_completed']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'mechanic.assigned']);
        $this->assertDatabaseHas('notifications', ['user_id' => $mechanic->id, 'title' => 'Pekerjaan Baru']);
    }

    public function test_courier_lain_tidak_dapat_complete_pickup(): void
    {
        [, $task] = $this->makeTask();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $courierLain = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $task->update([
            'courier_id' => $courier->id,
            'status'     => 'in_progress',
        ]);
        $tokenLain = $courierLain->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$tokenLain}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/complete")
            ->assertStatus(403);
    }

    public function test_alur_penuh_pickup_dari_confirm_hingga_mekanik_ditugaskan(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $mechanic = User::factory()->create([
            'role'           => 'mechanic',
            'is_active'      => true,
            'specialization' => 'mekanik_4_tak',
        ]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id'      => $customer->id,
            'nomor_polisi' => 'B1234XYZ',
            'merk'         => 'Honda',
            'model'        => 'Vario',
            'tahun'        => 2022,
            'tipe_mesin'   => '4_tak',
            'transmisi'    => 'matic',
        ]);

        $bookingService = app(\App\Services\BookingService::class);
        $booking = $bookingService->create([
            'vehicle_id'            => $vehicle->id,
            'tanggal'               => now()->addDay()->toDateString(),
            'waktu'                 => '10:00',
            'keluhan'               => 'Ganti oli dan servis rutin',
            'jenis_layanan'         => 'service_rutin',
            'pickup_requested'      => true,
            'alamat_pickup'         => 'Jl. Merdeka No. 45',
            'estimated_distance_km' => 5,
        ], $customer->id);

        $this->assertEquals('pending', $booking->status);
        $task = $booking->pickupTask;
        $this->assertNotNull($task);

        // 1. Admin konfirmasi booking -> status otomatis waiting_pickup
        $bookingService->confirm($booking);
        $booking->refresh();
        $this->assertEquals('waiting_pickup', $booking->status);

        // 2. Admin assign kurir
        $adminToken = $admin->createToken('admin')->plainTextToken;
        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/assign", ['courier_id' => $courier->id])
            ->assertOk();

        // 3. Kurir mulai task pickup
        $courierToken = $courier->createToken('courier')->plainTextToken;
        $this->withHeader('Authorization', "Bearer {$courierToken}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/start")
            ->assertOk();

        // 4. Kurir menyelesaikan task pickup
        $response = $this->withHeader('Authorization', "Bearer {$courierToken}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/complete");

        $response->assertOk();
        $task->refresh();
        $this->assertEquals('completed', $task->status);

        // 5. Booking otomatis assigned dan ServiceOrder otomatis dibuat tanpa konfirmasi web manual oleh admin
        $booking->refresh();
        $this->assertEquals('assigned', $booking->status);
        $this->assertNotNull($booking->serviceOrder);
        $this->assertEquals($mechanic->id, $booking->serviceOrder->mechanic_id);
        $this->assertEquals('pending', $booking->serviceOrder->status);
    }

    public function test_idempotensi_penyelesaian_pickup_tidak_membuat_service_order_duplikat(): void
    {
        [, $task] = $this->makeTask();
        $mechanic = User::factory()->create([
            'role'           => 'mechanic',
            'is_active'      => true,
            'specialization' => 'mekanik_4_tak',
        ]);
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $task->update([
            'courier_id' => $courier->id,
            'status'     => 'in_progress',
        ]);
        $token = $courier->createToken('t')->plainTextToken;

        // Panggilan pertama berhasil
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/complete")
            ->assertOk();

        $this->assertEquals(1, ServiceOrder::where('booking_id', $task->booking_id)->count());

        // Panggilan kedua ditolak 422 dan tidak menduplikasi ServiceOrder atau audit log
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/complete")
            ->assertStatus(422);

        $this->assertEquals(1, ServiceOrder::where('booking_id', $task->booking_id)->count());
        $this->assertEquals(1, \App\Models\AuditLog::where('action', 'delivery.pickup_completed')->where('entity_id', $task->id)->count());
    }

    public function test_auditlog_tercatat_saat_assign(): void
    {
        [, $task] = $this->makeTask();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/assign", ['courier_id' => $courier->id]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'delivery_task.assigned']);
    }

    public function test_notification_terkirim_saat_assign(): void
    {
        [, $task] = $this->makeTask();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/delivery-tasks/{$task->id}/assign", ['courier_id' => $courier->id]);

        $this->assertDatabaseHas('notifications', ['user_id' => $courier->id, 'title' => 'Tugas Baru']);
    }

    public function test_cancel_booking_otomatis_batalkan_delivery_task_aktif(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id'      => $customer->id,
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'CL',
            'merk'         => 'Honda',
            'model'        => 'Beat',
            'tahun'        => 2021,
            'tipe_mesin'   => '4_tak',
            'transmisi'    => 'matic',
        ]);

        $bookingService = app(\App\Services\BookingService::class);
        $booking = $bookingService->create([
            'vehicle_id'            => $vehicle->id,
            'tanggal'               => now()->addDay()->toDateString(),
            'waktu'                 => '10:00',
            'keluhan'               => 'Pickup test',
            'jenis_layanan'         => 'service_rutin',
            'pickup_requested'      => true,
            'alamat_pickup'         => 'Jl. Thamrin No. 1',
            'estimated_distance_km' => 3,
        ], $customer->id);

        $task = $booking->pickupTask;
        $this->assertNotNull($task);
        $this->assertEquals('pending', $task->status);

        // Assign courier
        $bookingService->confirm($booking);
        $task->update(['courier_id' => $courier->id, 'status' => 'assigned']);

        // Cancel booking via customer
        $customerToken = $customer->createToken('c')->plainTextToken;
        $this->withHeader('Authorization', "Bearer {$customerToken}")
            ->postJson("/api/v1/bookings/{$booking->id}/cancel", [
                'cancellation_reason' => 'Batal rencana',
            ])
            ->assertOk();

        $booking->refresh();
        $task->refresh();

        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('cancelled', $task->status);
        $this->assertStringContainsString('Dibatalkan bersama booking', $task->notes);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'delivery_task.cancelled',
            'entity_id' => $task->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $courier->id,
            'title'   => 'Tugas Dibatalkan',
        ]);
    }

    public function test_cancel_booking_tidak_mengubah_delivery_task_yang_sudah_completed(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id'      => $customer->id,
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'OK',
            'merk'         => 'Yamaha',
            'model'        => 'NMAX',
            'tahun'        => 2020,
            'tipe_mesin'   => '4_tak',
            'transmisi'    => 'matic',
        ]);

        $booking = Booking::create([
            'nomor_booking'    => Booking::generateNomorBooking(),
            'customer_id'      => $customer->id,
            'vehicle_id'       => $vehicle->id,
            'tanggal'          => now()->addDay(),
            'waktu'            => '10:00',
            'keluhan'          => 'Test',
            'jenis_layanan'    => 'perbaikan',
            'status'           => 'waiting_pickup',
            'pickup_requested' => true,
        ]);

        $task = DeliveryTask::create([
            'booking_id'   => $booking->id,
            'courier_id'   => $courier->id,
            'type'         => 'pickup',
            'address'      => 'Jl. Sudirman',
            'distance_km'  => 5,
            'delivery_fee' => 15000,
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        $bookingService = app(\App\Services\BookingService::class);
        $bookingService->cancel($booking, 'Alasan');

        $task->refresh();
        $this->assertEquals('completed', $task->status);
    }
}
