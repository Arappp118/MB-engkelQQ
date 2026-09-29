<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\ServiceItem;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeOrder(float $itemPrice = 100000, float $deliveryFee = 10000): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B' . rand(1000, 9999) . 'IA',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'completed',
        ]);
        $order = ServiceOrder::create(['booking_id' => $booking->id, 'mechanic_id' => $mechanic->id, 'status' => 'completed', 'delivery_fee' => $deliveryFee]);
        $item = ServiceItem::create(['category' => '4_tak', 'name' => 'Oli', 'price' => $itemPrice, 'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true]);
        ServiceOrderItem::create(['service_order_id' => $order->id, 'service_item_id' => $item->id, 'item_name_snapshot' => $item->name, 'price_snapshot' => $itemPrice, 'quantity' => 1, 'subtotal' => $itemPrice]);
        $order->recalculate();
        Payment::create(['service_order_id' => $order->id, 'payment_method' => 'cash', 'amount' => $order->grand_total, 'status' => 'verified']);

        return [$customer, $order, $item];
    }

    public function test_customer_hanya_melihat_invoice_miliknya(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/invoices/{$order->id}")->assertOk();
    }

    public function test_idor_customer_lain_ditolak(): void
    {
        [, $order] = $this->makeOrder();
        $customerLain = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customerLain->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/invoices/{$order->id}")->assertStatus(403);
    }

    public function test_invoice_pakai_price_snapshot_bukan_master(): void
    {
        [$customer, $order, $item] = $this->makeOrder(100000);
        $item->update(['price' => 999999]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/invoices/{$order->id}");

        $response->assertJsonPath('data.items.0.price', 100000);
    }

    public function test_invoice_pakai_delivery_fee_snapshot(): void
    {
        [$customer, $order] = $this->makeOrder(100000, 15000);
        \App\Models\DeliverySetting::set('price_per_km', '99999');
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/invoices/{$order->id}");

        $response->assertJsonPath('data.delivery_fee', 15000);
    }

    public function test_invoice_number_unik(): void
    {
        [$customer1, $order1] = $this->makeOrder();
        [$customer2, $order2] = $this->makeOrder();

        $r1 = $this->withHeader('Authorization', "Bearer {$customer1->createToken('t')->plainTextToken}")
            ->getJson("/api/v1/invoices/{$order1->id}");
        $r2 = $this->withHeader('Authorization', "Bearer {$customer2->createToken('t')->plainTextToken}")
            ->getJson("/api/v1/invoices/{$order2->id}");

        $this->assertNotEquals($r1->json('data.invoice_number'), $r2->json('data.invoice_number'));
    }

    public function test_payment_information_tampil(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/invoices/{$order->id}");

        $response->assertJsonPath('data.payment_status', 'verified');
    }

    public function test_invoice_read_only_tidak_ada_endpoint_write(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/invoices/{$order->id}", [])->assertStatus(405);
    }

    public function test_manipulasi_grand_total_via_query_tidak_berpengaruh(): void
    {
        [$customer, $order] = $this->makeOrder(100000, 10000);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/invoices/{$order->id}?grand_total=1");

        $response->assertJsonPath('data.grand_total', 110000);
    }
}
