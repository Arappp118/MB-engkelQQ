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
}
