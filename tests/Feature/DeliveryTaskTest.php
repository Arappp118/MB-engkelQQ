<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DeliverySetting;
use App\Models\DeliveryTask;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryTaskTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUsers(): array
    {
        return [
            User::factory()->create(['role' => 'admin', 'is_active' => true]),
            User::factory()->create(['role' => 'courier', 'is_active' => true]),
            User::factory()->create(['role' => 'customer', 'is_active' => true]),
            User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']),
        ];
    }

    protected function makeBookingWithPickupTask(User $customer, float $distanceKm = 5): array
    {
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'DV',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(),
            'waktu' => '10:00',
            'keluhan' => 'Servis rutin',
            'jenis_layanan' => 'service_rutin',
            'pickup_requested' => true,
            'alamat_pickup' => 'Jl. Contoh No. 1',
            'estimated_distance_km' => $distanceKm,
            'status' => 'confirmed',
        ]);

        $task = DeliveryTask::create([
            'booking_id' => $booking->id,
            'courier_id' => null,
            'type' => 'pickup',
            'address' => $booking->alamat_pickup,
            'distance_km' => $distanceKm,
            'delivery_fee' => 10000,
            'status' => 'pending',
        ]);

        return [$booking, $task];
    }

    public function test_1_admin_dapat_melihat_delivery_settings(): void
    {
        [$admin] = $this->makeUsers();

        $response = $this->actingAs($admin)->get('/delivery-settings');

        $response->assertOk();
        $response->assertViewIs('delivery-settings.index');
        $response->assertViewHas('pricePerKm');
    }

    public function test_2_admin_dapat_mengubah_price_per_km(): void
    {
        [$admin] = $this->makeUsers();

        $response = $this->actingAs($admin)->patch('/delivery-settings', ['price_per_km' => 3500]);

        $response->assertRedirect();
        $this->assertEquals(3500, (float) DeliverySetting::get('price_per_km'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'delivery.tariff_changed']);
    }

    public function test_3_courier_tidak_dapat_mengubah_price_per_km(): void
    {
        [, $courier] = $this->makeUsers();

        $response = $this->actingAs($courier)->patch('/delivery-settings', ['price_per_km' => 1]);

        $response->assertForbidden();
    }

    public function test_4_customer_tidak_dapat_mengubah_price_per_km(): void
    {
        [, , $customer] = $this->makeUsers();

        $response = $this->actingAs($customer)->patch('/delivery-settings', ['price_per_km' => 1]);

        $response->assertForbidden();
    }

    public function test_5_mechanic_tidak_dapat_mengubah_price_per_km(): void
    {
        [, , , $mechanic] = $this->makeUsers();

        $response = $this->actingAs($mechanic)->patch('/delivery-settings', ['price_per_km' => 1]);

        $response->assertForbidden();
    }

    public function test_6_delivery_fee_dihitung_server_side(): void
    {
        [$admin] = $this->makeUsers();
        DeliverySetting::set('price_per_km', '2000');

        $service = app(\App\Services\DeliveryTaskService::class);
        $fee = $service->calculateFee(5);

        $this->assertEquals(10000, $fee); // 5 x 2000, rounded to nearest 500
    }

    public function test_7_manipulasi_delivery_fee_via_request_diabaikan(): void
    {
        [$admin, $courier, $customer] = $this->makeUsers();
        [, $task] = $this->makeBookingWithPickupTask($customer);

        $this->actingAs($admin)->patch("/delivery-tasks/{$task->id}/assign", [
            'courier_id' => $courier->id,
            'delivery_fee' => 999999, // percobaan manipulasi
        ]);

        $this->assertDatabaseHas('delivery_tasks', [
            'id' => $task->id,
            'delivery_fee' => 10000, // tetap nilai asli, tidak berubah
        ]);
    }

    public function test_8_manipulasi_price_per_km_via_request_diabaikan(): void
    {
        [$admin] = $this->makeUsers();
        DeliverySetting::set('price_per_km', '2000');

        // Field selain price_per_km diabaikan karena tidak ada di rules()
        $this->actingAs($admin)->patch('/delivery-settings', [
            'price_per_km' => 2500,
            'some_other_field' => 'hacked',
        ]);

        $this->assertEquals(2500, (float) DeliverySetting::get('price_per_km'));
    }

    public function test_9_distance_negatif_ditolak_saat_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B999XY',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);

        $response = $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '10:00',
            'keluhan' => 'Rem bunyi',
            'jenis_layanan' => 'perbaikan',
            'pickup_requested' => true,
            'alamat_pickup' => 'Jl. Test',
            'estimated_distance_km' => -5,
        ]);

        $response->assertSessionHasErrors('estimated_distance_km');
    }

    public function test_10_courier_dapat_melihat_task_miliknya(): void
    {
        [, $courier, $customer] = $this->makeUsers();
        [, $task] = $this->makeBookingWithPickupTask($customer);
        $task->update(['courier_id' => $courier->id]);

        $response = $this->actingAs($courier)->get("/delivery-tasks/{$task->id}");

        $response->assertStatus(200);
        $response->assertViewIs('delivery-tasks.show');
    }

    public function test_11_courier_tidak_dapat_akses_task_courier_lain(): void
    {
        [, $courier, $customer] = $this->makeUsers();
        $courierLain = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        [, $task] = $this->makeBookingWithPickupTask($customer);
        $task->update(['courier_id' => $courier->id]);

        $response = $this->actingAs($courierLain)->get("/delivery-tasks/{$task->id}");

        $response->assertForbidden();
    }

    public function test_12_customer_tidak_dapat_mengambil_alih_task_courier(): void
    {
        [, , $customer] = $this->makeUsers();
        [, $task] = $this->makeBookingWithPickupTask($customer);

        $response = $this->actingAs($customer)->patch("/delivery-tasks/{$task->id}/assign", [
            'courier_id' => $customer->id,
        ]);

        $response->assertForbidden();
    }

    public function test_13_pickup_workflow_berhasil(): void
    {
        [$admin, $courier, $customer] = $this->makeUsers();
        [$booking, $task] = $this->makeBookingWithPickupTask($customer);

        $this->actingAs($admin)->patch("/delivery-tasks/{$task->id}/assign", ['courier_id' => $courier->id]);
        $this->actingAs($courier)->patch("/delivery-tasks/{$task->id}/start");
        $response = $this->actingAs($courier)->patch("/delivery-tasks/{$task->id}/complete");

        $response->assertRedirect();
        $this->assertDatabaseHas('delivery_tasks', ['id' => $task->id, 'status' => 'completed']);
        $booking->refresh();
        $this->assertEquals('vehicle_picked_up', $booking->status);
    }

    public function test_14_delivery_workflow_berhasil(): void
    {
        [$admin, $courier, $customer] = $this->makeUsers();
        [$booking] = $this->makeBookingWithPickupTask($customer);

        $deliveryTask = DeliveryTask::create([
            'booking_id' => $booking->id, 'courier_id' => null, 'type' => 'delivery',
            'address' => $booking->alamat_pickup, 'distance_km' => 5,
            'delivery_fee' => 10000, 'status' => 'pending',
        ]);

        $this->actingAs($admin)->patch("/delivery-tasks/{$deliveryTask->id}/assign", ['courier_id' => $courier->id]);
        $this->actingAs($courier)->patch("/delivery-tasks/{$deliveryTask->id}/start");
        $response = $this->actingAs($courier)->patch("/delivery-tasks/{$deliveryTask->id}/complete");

        $response->assertRedirect();
        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
    }

    public function test_15_payment_verified_tidak_membuat_delivery_task(): void
    {
        [$admin, , $customer, $mechanic] = $this->makeUsers();
        [$booking] = $this->makeBookingWithPickupTask($customer);

        $order = ServiceOrder::create([
            'booking_id' => $booking->id, 'mechanic_id' => $mechanic->id,
            'status' => 'completed', 'grand_total' => 100000, 'delivery_fee' => 10000,
        ]);

        $countBefore = DeliveryTask::where('booking_id', $booking->id)->count(); // 1 (pickup)

        $payment = Payment::create([
            'service_order_id' => $order->id, 'payment_method' => 'cash',
            'amount' => $order->grand_total, 'status' => 'waiting_verification',
        ]);

        $this->actingAs($admin)->patch("/payments/{$payment->id}/verify");

        $countAfter = DeliveryTask::where('booking_id', $booking->id)->count();

        $this->assertEquals($countBefore, $countAfter); // Tidak ada tambahan delivery task
    }

    public function test_16_perubahan_tarif_tidak_mengubah_fee_transaksi_lama(): void
    {
        [$admin, , $customer] = $this->makeUsers();
        DeliverySetting::set('price_per_km', '2000');

        [, $task] = $this->makeBookingWithPickupTask($customer, 5); // fee sudah fixed 10000 saat dibuat

        $this->actingAs($admin)->patch('/delivery-settings', ['price_per_km' => 5000]);

        $task->refresh();
        $this->assertEquals(10000, $task->delivery_fee); // tidak berubah jadi 25000
    }

    public function test_17_auditlog_tercatat_saat_assign_dan_complete(): void
    {
        [$admin, $courier, $customer] = $this->makeUsers();
        [, $task] = $this->makeBookingWithPickupTask($customer);

        $this->actingAs($admin)->patch("/delivery-tasks/{$task->id}/assign", ['courier_id' => $courier->id]);
        $this->actingAs($courier)->patch("/delivery-tasks/{$task->id}/start");
        $this->actingAs($courier)->patch("/delivery-tasks/{$task->id}/complete");

        $this->assertDatabaseHas('audit_logs', ['action' => 'delivery_task.assigned']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'delivery.started']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'delivery.pickup_completed']);
    }

    public function test_18_unauthorized_assign_menghasilkan_403(): void
    {
        [, , $customer, $mechanic] = $this->makeUsers();
        [, $task] = $this->makeBookingWithPickupTask($customer);

        $response = $this->actingAs($mechanic)->patch("/delivery-tasks/{$task->id}/assign", [
            'courier_id' => $customer->id,
        ]);

        $response->assertForbidden();
    }
    public function test_19_task_pending_tanpa_courier_tidak_dapat_start(): void
    {
        [$admin, $courier, $customer] = $this->makeUsers();
        [, $task] = $this->makeBookingWithPickupTask($customer);

        $response = $this->actingAs($admin)->patch("/delivery-tasks/{$task->id}/start");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('delivery_tasks', [
            'id' => $task->id,
            'status' => 'pending',
        ]);
    }

    public function test_20_task_tanpa_courier_tidak_dapat_complete(): void
    {
        [$admin, $courier, $customer] = $this->makeUsers();
        [, $task] = $this->makeBookingWithPickupTask($customer);

        $task->update(['status' => 'in_progress', 'courier_id' => null]);

        $response = $this->actingAs($admin)->patch("/delivery-tasks/{$task->id}/complete");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('delivery_tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }
}
