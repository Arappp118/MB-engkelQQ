<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function makeBookingWithOrder(string $mechanicSpecialization = 'mekanik_4_tak'): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create([
            'role' => 'mechanic',
            'specialization' => $mechanicSpecialization,
            'is_active' => true,
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'ZZ',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(),
            'waktu' => '10:00',
            'keluhan' => 'Rem depan bunyi',
            'jenis_layanan' => 'perbaikan',
            'status' => 'assigned',
        ]);

        $order = ServiceOrder::create([
            'booking_id' => $booking->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'pending',
        ]);

        return [$customer, $mechanic, $booking, $order];
    }

    public function test_mekanik_dapat_melihat_service_order_miliknya(): void
    {
        [, $mechanic, , $order] = $this->makeBookingWithOrder();

        $response = $this->actingAs($mechanic)->get("/service-orders/{$order->id}");

        $response->assertOk();
        $response->assertViewIs('service-orders.show');
        $response->assertViewHas('serviceOrder', fn ($so) => $so->id === $order->id);
        $response->assertSee('Detail Service Order');
    }

    public function test_mekanik_tidak_dapat_mengakses_service_order_mekanik_lain(): void
    {
        [, , , $order] = $this->makeBookingWithOrder();
        $mechanicLain = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);

        $response = $this->actingAs($mechanicLain)->get("/service-orders/{$order->id}");

        $response->assertForbidden();
    }

    public function test_customer_tidak_dapat_mengubah_diagnosis(): void
    {
        [$customer, , , $order] = $this->makeBookingWithOrder();

        $response = $this->actingAs($customer)->patch("/service-orders/{$order->id}/diagnosis", [
            'diagnosis_mechanic' => 'Diagnosis palsu dari customer',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('service_orders', [
            'id' => $order->id,
            'diagnosis_mechanic' => 'Diagnosis palsu dari customer',
        ]);
    }

    public function test_mekanik_dapat_menyimpan_diagnosis(): void
    {
        [, $mechanic, , $order] = $this->makeBookingWithOrder();

        $response = $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/diagnosis", [
            'diagnosis_mechanic' => 'Kampas rem habis, perlu ganti',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_orders', [
            'id' => $order->id,
            'diagnosis_mechanic' => 'Kampas rem habis, perlu ganti',
        ]);
    }

    public function test_mekanik_dapat_memulai_servis_dari_status_pending(): void
    {
        [, $mechanic, , $order] = $this->makeBookingWithOrder();

        $response = $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/start");

        $response->assertRedirect();
        $this->assertDatabaseHas('service_orders', [
            'id' => $order->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_tidak_dapat_memulai_servis_yang_sudah_in_progress(): void
    {
        [, $mechanic, , $order] = $this->makeBookingWithOrder();
        $order->update(['status' => 'in_progress']);

        $response = $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/start");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('service_orders', [
            'id' => $order->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_tidak_dapat_menyelesaikan_servis_tanpa_diagnosis(): void
    {
        [, $mechanic, , $order] = $this->makeBookingWithOrder();
        $order->update(['status' => 'in_progress']);

        $response = $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/complete");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('service_orders', [
            'id' => $order->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_auditlog_tercatat_saat_diagnosis_disimpan(): void
    {
        [, $mechanic, , $order] = $this->makeBookingWithOrder();

        $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/diagnosis", [
            'diagnosis_mechanic' => 'Test diagnosis',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'service_order.diagnosis_saved',
            'entity_id' => $order->id,
        ]);
    }

    // ── View: index ───────────────────────────────────────────────────────────

    public function test_customer_dapat_melihat_index_service_order(): void
    {
        [$customer, , , $order] = $this->makeBookingWithOrder();

        $response = $this->actingAs($customer)->get('/service-orders');

        $response->assertOk();
        $response->assertViewIs('service-orders.index');
        $response->assertSee('Service Order');
    }

    public function test_mekanik_dapat_melihat_index_service_order_miliknya(): void
    {
        [, $mechanic, $booking] = $this->makeBookingWithOrder();

        $response = $this->actingAs($mechanic)->get('/service-orders');

        $response->assertOk();
        $response->assertViewIs('service-orders.index');
        $response->assertSee($booking->nomor_booking);
    }

    public function test_customer_dapat_melihat_detail_service_order_miliknya(): void
    {
        [$customer, , , $order] = $this->makeBookingWithOrder();

        $response = $this->actingAs($customer)->get("/service-orders/{$order->id}");

        $response->assertOk();
        $response->assertViewIs('service-orders.show');
        $response->assertViewHas('serviceOrder', fn ($so) => $so->id === $order->id);
    }

    public function test_customer_tidak_dapat_melihat_service_order_orang_lain(): void
    {
        [, , , $order] = $this->makeBookingWithOrder();
        $otherCustomer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $response = $this->actingAs($otherCustomer)->get("/service-orders/{$order->id}");

        $response->assertForbidden();
    }
}
