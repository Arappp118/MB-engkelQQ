<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\DeliverySetting;
use App\Models\DeliveryTask;
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
}
