<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\ServiceItem;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
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

    public function test_add_item_unauthenticated_menghasilkan_401(): void
    {
        [, , $order] = $this->makeOrder();
        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 100000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $this->postJson("/api/v1/service-orders/{$order->id}/items", [
            'service_item_id' => $item->id,
            'quantity' => 1,
        ])->assertStatus(401);
    }

    public function test_mechanic_dapat_menambah_item_ke_order_miliknya(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli MPX', 'price' => 85000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Item berhasil ditambahkan',
            'data' => [
                'id' => $order->id,
                'subtotal' => 170000,
                'grand_total' => 170000,
            ],
        ]);

        $this->assertDatabaseHas('service_order_items', [
            'service_order_id'   => $order->id,
            'service_item_id'    => $item->id,
            'item_name_snapshot' => 'Ganti Oli MPX',
            'price_snapshot'     => 85000,
            'quantity'           => 2,
            'subtotal'           => 170000,
        ]);

        $order->refresh();
        $this->assertEquals(170000, $order->subtotal);
        $this->assertEquals(170000, $order->grand_total);
    }

    public function test_manipulasi_harga_dan_subtotal_via_request_diabaikan(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Tune Up', 'price' => 120000,
            'unit' => 'jasa', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 1,
                'price_snapshot' => 1000, // Percobaan manipulasi harga
                'price' => 500,           // Percobaan manipulasi harga
                'subtotal' => 100,        // Percobaan manipulasi subtotal
                'grand_total' => 200,     // Percobaan manipulasi grand total
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $order->id,
            'price_snapshot' => 120000, // Tetap harga katalog database
            'subtotal' => 120000,
        ]);

        $this->assertDatabaseMissing('service_order_items', [
            'service_order_id' => $order->id,
            'price_snapshot' => 1000,
        ]);

        $order->refresh();
        $this->assertEquals(120000, $order->subtotal);
        $this->assertEquals(120000, $order->grand_total);
    }

    public function test_sparepart_dengan_stok_cukup_berhasil_dan_stok_berkurang(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $sparepart = ServiceItem::create([
            'category' => 'sparepart', 'name' => 'Busi NGK', 'price' => 35000,
            'unit' => 'pcs', 'is_sparepart' => true, 'stock' => 10, 'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $sparepart->id,
                'quantity' => 3,
            ]);

        $response->assertStatus(201);

        $sparepart->refresh();
        $this->assertEquals(7, $sparepart->stock);

        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $order->id,
            'service_item_id' => $sparepart->id,
            'quantity' => 3,
            'subtotal' => 105000,
        ]);
    }

    public function test_sparepart_dengan_stok_tidak_cukup_ditolak_422_dan_stok_tidak_berkurang(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $sparepart = ServiceItem::create([
            'category' => 'sparepart', 'name' => 'Kampas Rem', 'price' => 50000,
            'unit' => 'set', 'is_sparepart' => true, 'stock' => 2, 'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $sparepart->id,
                'quantity' => 5,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('tidak mencukupi', $response->json('message'));

        $sparepart->refresh();
        $this->assertEquals(2, $sparepart->stock);

        $this->assertDatabaseMissing('service_order_items', [
            'service_order_id' => $order->id,
            'service_item_id' => $sparepart->id,
        ]);
    }

    public function test_item_nonaktif_ditolak_422(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Oli Jadul', 'price' => 50000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => false,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('service_order_items', [
            'service_order_id' => $order->id,
        ]);
    }

    public function test_quantity_invalid_ditolak_422(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 50000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        // Quantity 0
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 0,
            ])->assertStatus(422);

        // Quantity negatif
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => -2,
            ])->assertStatus(422);

        // Quantity > 1000
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 1001,
            ])->assertStatus(422);
    }

    public function test_idor_mechanic_lain_tidak_dapat_tambah_item_403(): void
    {
        [, , $order] = $this->makeOrder();
        $mechanicLain = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $token = $mechanicLain->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 50000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 1,
            ])->assertStatus(403);
    }

    public function test_customer_tidak_dapat_tambah_item_403(): void
    {
        [$customer, , $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 50000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 1,
            ])->assertStatus(403);
    }

    public function test_courier_tidak_dapat_tambah_item_403(): void
    {
        [, , $order] = $this->makeOrder();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $token = $courier->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 50000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 1,
            ])->assertStatus(403);
    }

    public function test_admin_dapat_tambah_item_ke_semua_order_201(): void
    {
        [, , $order] = $this->makeOrder();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Jasa Servis Karbu', 'price' => 60000,
            'unit' => 'jasa', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $order->id,
            'service_item_id' => $item->id,
        ]);
    }

    public function test_service_order_tidak_ditemukan_menghasilkan_404(): void
    {
        [, $mechanic] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 50000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/service-orders/999999/items', [
                'service_item_id' => $item->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Data tidak ditemukan.',
        ]);
    }

    public function test_auditlog_tercatat_saat_item_ditambahkan(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $token = $mechanic->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Kampas Kopling', 'price' => 150000,
            'unit' => 'set', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 1,
            ])->assertStatus(201);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'service_order.item_added',
            'entity_type' => 'ServiceOrder',
            'entity_id' => $order->id,
        ]);
    }

    public function test_penambahan_item_pada_order_completed_ditolak(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $order->update(['status' => 'completed']);
        $token = $mechanic->createToken('t')->plainTextToken;

        $sparepart = ServiceItem::create([
            'category' => 'sparepart', 'name' => 'Busi Iridium', 'price' => 75000,
            'unit' => 'pcs', 'is_sparepart' => true, 'stock' => 10, 'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $sparepart->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => "Tidak dapat menambah item pada service order berstatus 'completed'.",
        ]);

        $sparepart->refresh();
        $this->assertEquals(10, $sparepart->stock);

        $this->assertDatabaseMissing('service_order_items', [
            'service_order_id' => $order->id,
            'service_item_id'  => $sparepart->id,
        ]);

        $order->refresh();
        $this->assertEquals(0, $order->subtotal);
        $this->assertEquals(0, $order->grand_total);
    }

    public function test_penambahan_item_pada_order_cancelled_ditolak(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $order->update(['status' => 'cancelled']);
        $token = $mechanic->createToken('t')->plainTextToken;

        $sparepart = ServiceItem::create([
            'category' => 'sparepart', 'name' => 'Busi Standar', 'price' => 25000,
            'unit' => 'pcs', 'is_sparepart' => true, 'stock' => 5, 'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $sparepart->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => "Tidak dapat menambah item pada service order berstatus 'cancelled'.",
        ]);

        $sparepart->refresh();
        $this->assertEquals(5, $sparepart->stock);

        $this->assertDatabaseMissing('service_order_items', [
            'service_order_id' => $order->id,
            'service_item_id'  => $sparepart->id,
        ]);
    }

    public function test_penambahan_item_pada_order_in_progress_berhasil(): void
    {
        [, $mechanic, $order] = $this->makeOrder();
        $order->update(['status' => 'in_progress']);
        $token = $mechanic->createToken('t')->plainTextToken;

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Jasa Tune Up', 'price' => 80000,
            'unit' => 'jasa', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/items", [
                'service_item_id' => $item->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Item berhasil ditambahkan',
        ]);

        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $order->id,
            'service_item_id'  => $item->id,
        ]);

        $order->refresh();
        $this->assertEquals(80000, $order->subtotal);
        $this->assertEquals(80000, $order->grand_total);
    }
}
