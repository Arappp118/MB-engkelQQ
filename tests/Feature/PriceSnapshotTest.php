<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ServiceItem;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function makeOrder(): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']);

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'PS',
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
            'status' => 'assigned',
        ]);

        $order = ServiceOrder::create([
            'booking_id' => $booking->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'pending',
        ]);

        return [$mechanic, $order];
    }

    public function test_1_snapshot_price_sesuai_harga_master_saat_ditambahkan(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 100000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $response = $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $item->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $order->id,
            'price_snapshot' => 100000,
            'item_name_snapshot' => 'Ganti Oli',
        ]);
    }

    public function test_2_perubahan_harga_master_tidak_mengubah_snapshot_lama(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 100000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $item->id,
            'quantity' => 1,
        ]);

        $orderItem = ServiceOrderItem::where('service_order_id', $order->id)->first();

        // Ubah harga master
        $item->update(['price' => 150000]);

        $orderItem->refresh();
        $this->assertEquals(100000, $orderItem->price_snapshot);
    }

    public function test_3_transaksi_baru_setelah_harga_berubah_pakai_harga_baru(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 100000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $item->update(['price' => 150000]);

        $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $item->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $order->id,
            'price_snapshot' => 150000,
        ]);
    }

    public function test_4_manipulasi_price_snapshot_via_request_diabaikan(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 100000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $item->id,
            'quantity' => 1,
            'price_snapshot' => 1, // percobaan manipulasi
        ]);

        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $order->id,
            'price_snapshot' => 100000, // tetap harga asli dari DB, bukan 1
        ]);
        $this->assertDatabaseMissing('service_order_items', [
            'price_snapshot' => 1,
        ]);
    }

    public function test_5_manipulasi_subtotal_via_request_dihitung_ulang_server(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 100000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $item->id,
            'quantity' => 2,
            'subtotal' => 1, // percobaan manipulasi
        ]);

        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $order->id,
            'subtotal' => 200000, // 100000 x 2, dihitung server
        ]);
    }

    public function test_6_quantity_divalidasi(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 100000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $response = $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $item->id,
            'quantity' => 0, // invalid
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseMissing('service_order_items', [
            'service_order_id' => $order->id,
        ]);
    }

    public function test_7_item_nonaktif_tidak_dapat_ditambahkan(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Item Lama', 'price' => 100000,
            'unit' => 'item', 'is_sparepart' => false, 'is_active' => false,
        ]);

        $response = $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $item->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('service_item_id');
        $this->assertDatabaseMissing('service_order_items', [
            'service_order_id' => $order->id,
        ]);
    }

    public function test_8_grand_total_terhitung_ulang_setelah_item_ditambahkan(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 100000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $item->id,
            'quantity' => 3,
        ]);

        $order->refresh();
        $this->assertEquals(300000, $order->subtotal);
        $this->assertEquals(300000, $order->grand_total);
    }

    public function test_9_sparepart_dengan_stok_cukup_berhasil_dan_stok_berkurang(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $sparepart = ServiceItem::create([
            'category' => 'sparepart', 'name' => 'Busi NGK', 'price' => 35000,
            'unit' => 'pcs', 'is_sparepart' => true, 'stock' => 10, 'is_active' => true,
        ]);

        $response = $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $sparepart->id,
            'quantity' => 3,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sparepart->refresh();
        $this->assertEquals(7, $sparepart->stock);

        $this->assertDatabaseHas('service_order_items', [
            'service_order_id'   => $order->id,
            'service_item_id'    => $sparepart->id,
            'item_name_snapshot' => 'Busi NGK',
            'price_snapshot'     => 35000,
            'quantity'           => 3,
            'subtotal'           => 105000,
        ]);
    }

    public function test_10_sparepart_dengan_stok_tidak_cukup_ditolak_dan_stok_tidak_berkurang(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $sparepart = ServiceItem::create([
            'category' => 'sparepart', 'name' => 'Kampas Rem', 'price' => 50000,
            'unit' => 'set', 'is_sparepart' => true, 'stock' => 2, 'is_active' => true,
        ]);

        $response = $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $sparepart->id,
            'quantity' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $sparepart->refresh();
        $this->assertEquals(2, $sparepart->stock);

        $this->assertDatabaseMissing('service_order_items', [
            'service_order_id' => $order->id,
            'service_item_id'  => $sparepart->id,
        ]);
    }

    public function test_11_item_snapshot_nama_dan_harga_selalu_terisi_lengkap_dan_subtotal_atomik(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $sparepart = ServiceItem::create([
            'category' => 'sparepart', 'name' => 'Filter Udara', 'price' => 45000,
            'unit' => 'pcs', 'is_sparepart' => true, 'stock' => 5, 'is_active' => true,
        ]);

        $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $sparepart->id,
            'quantity' => 2,
        ]);

        $orderItem = ServiceOrderItem::where('service_order_id', $order->id)->first();
        $this->assertNotNull($orderItem);
        $this->assertNotEmpty($orderItem->item_name_snapshot);
        $this->assertEquals('Filter Udara', $orderItem->item_name_snapshot);
        $this->assertEquals(45000, $orderItem->price_snapshot);
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals(90000, $orderItem->subtotal);

        $order->refresh();
        $this->assertEquals(90000, $order->subtotal);
        $this->assertEquals(90000, $order->grand_total);
    }

    public function test_12_penambahan_item_pada_order_completed_ditolak(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $order->update(['status' => 'completed']);
        $sparepart = ServiceItem::create([
            'category' => 'sparepart', 'name' => 'Busi Iridium', 'price' => 75000,
            'unit' => 'pcs', 'is_sparepart' => true, 'stock' => 10, 'is_active' => true,
        ]);

        $response = $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $sparepart->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', "Tidak dapat menambah item pada service order berstatus 'completed'.");

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

    public function test_13_penambahan_item_pada_order_cancelled_ditolak(): void
    {
        [$mechanic, $order] = $this->makeOrder();
        $order->update(['status' => 'cancelled']);
        $sparepart = ServiceItem::create([
            'category' => 'sparepart', 'name' => 'Busi Standar', 'price' => 25000,
            'unit' => 'pcs', 'is_sparepart' => true, 'stock' => 5, 'is_active' => true,
        ]);

        $response = $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $sparepart->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', "Tidak dapat menambah item pada service order berstatus 'cancelled'.");

        $sparepart->refresh();
        $this->assertEquals(5, $sparepart->stock);
        $this->assertDatabaseMissing('service_order_items', [
            'service_order_id' => $order->id,
            'service_item_id'  => $sparepart->id,
        ]);
    }
}
