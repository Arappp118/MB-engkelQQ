<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeOrder(): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B' . rand(1000, 9999) . 'SO',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'assigned',
        ]);
        $order = ServiceOrder::create(['booking_id' => $booking->id, 'mechanic_id' => $mechanic->id, 'status' => 'pending']);

        return [$customer, $mechanic, $order];
    }

    public function test_unauthenticated_menghasilkan_401(): void
    {
        [, , $order] = $this->makeOrder();
        $this->getJson("/api/v1/service-orders/{$order->id}")->assertStatus(401);
    }

    public function test_mechanic_dapat_melihat_order_miliknya(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/service-orders/{$order->id}")->assertOk();
    }

    public function test_mechanic_tidak_dapat_akses_order_mechanic_lain(): void
    {
        [, , $order] = $this->makeOrder();
        $mechanicLain = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $token = $mechanicLain->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/service-orders/{$order->id}")->assertStatus(403);
    }

    public function test_customer_dapat_melihat_order_miliknya(): void
    {
        [$customer, , $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/service-orders/{$order->id}")->assertOk();
    }

    public function test_customer_tidak_boleh_mengubah_diagnosis(): void
    {
        [$customer, , $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/diagnosis", [
                'diagnosis_mechanic' => 'Palsu dari customer',
            ])->assertStatus(403);

        $this->assertDatabaseMissing('service_orders', [
            'id' => $order->id, 'diagnosis_mechanic' => 'Palsu dari customer',
        ]);
    }

    public function test_mechanic_dapat_mengisi_diagnosis(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/diagnosis", [
                'diagnosis_mechanic' => 'Kampas rem habis',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('service_orders', ['id' => $order->id, 'diagnosis_mechanic' => 'Kampas rem habis']);
    }

    public function test_status_guard_tidak_bisa_start_dua_kali(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")->postJson("/api/v1/service-orders/{$order->id}/start");

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/start");

        $response->assertStatus(422);
    }

    public function test_idor_mechanic_lain_tidak_dapat_start(): void
    {
        [, , $order] = $this->makeOrder();
        $mechanicLain = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $token = $mechanicLain->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/start")->assertStatus(403);
    }

    public function test_notification_terkirim_saat_start(): void
    {
        [$customer, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")->postJson("/api/v1/service-orders/{$order->id}/start");

        $this->assertDatabaseHas('notifications', ['user_id' => $customer->id, 'title' => 'Kendaraan Mulai Diservis']);
    }

    public function test_auditlog_tercatat_saat_diagnosis(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/diagnosis", ['diagnosis_mechanic' => 'Test']);

        $this->assertDatabaseHas('audit_logs', ['action' => 'service_order.diagnosis_saved']);
    }

    public function test_response_tidak_membocorkan_field_sensitif(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/service-orders/{$order->id}");

        $response->assertJsonMissingPath('data.mechanic.password');
    }

    public function test_price_tidak_dapat_dikirim_mechanic_lewat_start(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/start", ['grand_total' => 999999]);

        $response->assertOk();
        $this->assertDatabaseMissing('service_orders', ['id' => $order->id, 'grand_total' => 999999]);
    }
}
