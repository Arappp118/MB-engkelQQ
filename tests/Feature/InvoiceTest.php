<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\ServiceItem;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCompletedOrder(float $itemPrice = 100000, float $deliveryFee = 10000): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'IV',
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
            'status' => 'completed',
        ]);

        $order = ServiceOrder::create([
            'booking_id' => $booking->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'completed',
            'delivery_fee' => $deliveryFee,
        ]);

        $item = ServiceItem::create([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => $itemPrice,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ]);

        ServiceOrderItem::create([
            'service_order_id' => $order->id,
            'service_item_id' => $item->id,
            'item_name_snapshot' => $item->name,
            'price_snapshot' => $itemPrice,
            'quantity' => 1,
            'subtotal' => $itemPrice,
        ]);

        $order->recalculate();

        Payment::create([
            'service_order_id' => $order->id, 'payment_method' => 'cash',
            'amount' => $order->grand_total, 'status' => 'verified',
        ]);

        return [$customer, $admin, $order, $item];
    }

    public function test_customer_hanya_melihat_invoice_miliknya(): void
    {
        [$customer, , $order] = $this->makeCompletedOrder();

        $response = $this->actingAs($customer)->get("/service-orders/{$order->id}/invoice");

        $response->assertOk();
        $response->assertJsonPath('grand_total', 110000);
    }

    public function test_customer_tidak_dapat_melihat_invoice_customer_lain(): void
    {
        [, , $order] = $this->makeCompletedOrder();
        $customerLain = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $response = $this->actingAs($customerLain)->get("/service-orders/{$order->id}/invoice");

        $response->assertForbidden();
    }

    public function test_invoice_menggunakan_price_snapshot(): void
    {
        [$customer, , $order, $item] = $this->makeCompletedOrder(100000);

        $item->update(['price' => 999999]); // ubah harga master setelah transaksi

        $response = $this->actingAs($customer)->get("/service-orders/{$order->id}/invoice");

        $response->assertJsonPath('items.0.price', 100000); // tetap snapshot lama
    }

    public function test_perubahan_master_price_tidak_mengubah_invoice_lama(): void
    {
        [$customer, , $order, $item] = $this->makeCompletedOrder(100000);
        $item->update(['price' => 500000]);

        $response = $this->actingAs($customer)->get("/service-orders/{$order->id}/invoice");

        $response->assertJsonPath('subtotal', 100000);
        $response->assertJsonPath('grand_total', 110000);
    }

    public function test_invoice_menggunakan_delivery_fee_snapshot(): void
    {
        [$customer, , $order] = $this->makeCompletedOrder(100000, 15000);

        $response = $this->actingAs($customer)->get("/service-orders/{$order->id}/invoice");

        $response->assertJsonPath('delivery_fee', 15000);
    }

    public function test_perubahan_tarif_delivery_tidak_mengubah_invoice_lama(): void
    {
        [$customer, , $order] = $this->makeCompletedOrder(100000, 10000);

        \App\Models\DeliverySetting::set('price_per_km', '99999');

        $response = $this->actingAs($customer)->get("/service-orders/{$order->id}/invoice");

        $response->assertJsonPath('delivery_fee', 10000); // tidak berubah
    }

    public function test_total_invoice_benar_dari_server(): void
    {
        [$customer, , $order] = $this->makeCompletedOrder(75000, 5000);

        $response = $this->actingAs($customer)->get("/service-orders/{$order->id}/invoice");

        $response->assertJsonPath('subtotal', 75000);
        $response->assertJsonPath('delivery_fee', 5000);
        $response->assertJsonPath('grand_total', 80000);
    }

    public function test_manipulasi_total_dari_request_tidak_berpengaruh(): void
    {
        [$customer, , $order] = $this->makeCompletedOrder(100000, 10000);

        // Invoice endpoint GET murni, tidak menerima input apapun untuk hitung ulang
        $response = $this->actingAs($customer)->get("/service-orders/{$order->id}/invoice?grand_total=1&total=1");

        $response->assertJsonPath('grand_total', 110000);
    }

    public function test_nomor_invoice_unik_per_order(): void
    {
        [$customer1, , $order1] = $this->makeCompletedOrder();
        [$customer2, , $order2] = $this->makeCompletedOrder();

        $response1 = $this->actingAs($customer1)->get("/service-orders/{$order1->id}/invoice");
        $response2 = $this->actingAs($customer2)->get("/service-orders/{$order2->id}/invoice");

        $this->assertNotEquals(
            $response1->json('invoice_number'),
            $response2->json('invoice_number')
        );
    }

    public function test_payment_status_dari_database(): void
    {
        [$customer, , $order] = $this->makeCompletedOrder();

        $response = $this->actingAs($customer)->get("/service-orders/{$order->id}/invoice");

        $response->assertJsonPath('payment_status', 'verified');
    }

    public function test_unauthorized_menghasilkan_403(): void
    {
        [, , $order] = $this->makeCompletedOrder();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);

        $response = $this->actingAs($courier)->get("/service-orders/{$order->id}/invoice");

        $response->assertForbidden();
    }

    public function test_mechanic_pemilik_order_dapat_melihat_invoice(): void
    {
        [, , $order] = $this->makeCompletedOrder();
        $mechanic = $order->mechanic;

        $response = $this->actingAs($mechanic)->get("/service-orders/{$order->id}/invoice");

        $response->assertOk();
    }
}
