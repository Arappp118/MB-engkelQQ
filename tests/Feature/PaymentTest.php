<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function makeOrderWithTotal(float $grandTotal = 200000): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'PY',
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
            'status' => 'service_completed',
        ]);

        $order = ServiceOrder::create([
            'booking_id' => $booking->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'completed',
            'grand_total' => $grandTotal,
        ]);

        return [$customer, $mechanic, $admin, $order];
    }

    public function test_1_customer_dapat_membuat_payment_untuk_order_miliknya(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal(200000);

        $response = $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'service_order_id' => $order->id,
            'amount' => 200000,
        ]);
    }

    public function test_2_customer_tidak_dapat_bayar_order_customer_lain(): void
    {
        [, , , $order] = $this->makeOrderWithTotal();
        $customerLain = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $response = $this->actingAs($customerLain)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'cash',
        ]);

        $response->assertForbidden();
    }

    public function test_3_customer_tidak_dapat_verify_payment(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal();
        $payment = Payment::create([
            'service_order_id' => $order->id, 'payment_method' => 'cash',
            'amount' => $order->grand_total, 'status' => 'waiting_verification',
        ]);

        $response = $this->actingAs($customer)->patch("/payments/{$payment->id}/verify");

        $response->assertForbidden();
    }

    public function test_4_mechanic_tidak_dapat_verify_payment(): void
    {
        [, $mechanic, , $order] = $this->makeOrderWithTotal();
        $payment = Payment::create([
            'service_order_id' => $order->id, 'payment_method' => 'cash',
            'amount' => $order->grand_total, 'status' => 'waiting_verification',
        ]);

        $response = $this->actingAs($mechanic)->patch("/payments/{$payment->id}/verify");

        $response->assertForbidden();
    }

    public function test_5_courier_tidak_dapat_verify_payment(): void
    {
        [, , , $order] = $this->makeOrderWithTotal();
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $payment = Payment::create([
            'service_order_id' => $order->id, 'payment_method' => 'cash',
            'amount' => $order->grand_total, 'status' => 'waiting_verification',
        ]);

        $response = $this->actingAs($courier)->patch("/payments/{$payment->id}/verify");

        $response->assertForbidden();
    }

    public function test_6_admin_dapat_verify_payment(): void
    {
        [, , $admin, $order] = $this->makeOrderWithTotal();
        $payment = Payment::create([
            'service_order_id' => $order->id, 'payment_method' => 'cash',
            'amount' => $order->grand_total, 'status' => 'waiting_verification',
        ]);

        $response = $this->actingAs($admin)->patch("/payments/{$payment->id}/verify");

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'verified']);
    }

    public function test_7_admin_dapat_reject_payment(): void
    {
        [, , $admin, $order] = $this->makeOrderWithTotal();
        $payment = Payment::create([
            'service_order_id' => $order->id, 'payment_method' => 'transfer',
            'amount' => $order->grand_total, 'status' => 'waiting_verification',
        ]);

        $response = $this->actingAs($admin)->patch("/payments/{$payment->id}/reject", [
            'reason' => 'Bukti transfer tidak valid',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'rejected']);
    }

    public function test_8_transfer_memerlukan_proof(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal();

        $response = $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'transfer',
        ]);

        $response->assertSessionHasErrors('proof');
    }

    public function test_9_cash_tidak_memerlukan_proof(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal();

        $response = $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors('proof');
    }

    public function test_10_invalid_file_ditolak(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal();
        $file = UploadedFile::fake()->create('malware.exe', 100);

        $response = $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'transfer',
            'proof' => $file,
        ]);

        $response->assertSessionHasErrors('proof');
    }

    public function test_11_file_terlalu_besar_ditolak(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal();
        $file = UploadedFile::fake()->create('bukti.jpg', 3000); // 3MB > limit 2MB

        $response = $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'transfer',
            'proof' => $file,
        ]);

        $response->assertSessionHasErrors('proof');
    }

    public function test_12_amount_dari_request_tidak_dapat_memanipulasi_total(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal(200000);

        $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'cash',
            'amount' => 1, // percobaan manipulasi
        ]);

        $this->assertDatabaseHas('payments', [
            'service_order_id' => $order->id,
            'amount' => 200000, // tetap dari grand_total server
        ]);
        $this->assertDatabaseMissing('payments', ['amount' => 1]);
    }

    public function test_13_total_payment_menggunakan_total_service_order_server(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal(350000);

        $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('payments', [
            'service_order_id' => $order->id,
            'amount' => 350000,
        ]);
    }

    public function test_14_customer_tidak_dapat_mengubah_status_menjadi_paid(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal();

        $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'cash',
            'status' => 'paid', // percobaan manipulasi
        ]);

        $this->assertDatabaseHas('payments', [
            'service_order_id' => $order->id,
            'status' => 'waiting_verification', // bukan 'paid'
        ]);
    }

    public function test_15_payment_verified_mengubah_status_booking(): void
    {
        [, , $admin, $order] = $this->makeOrderWithTotal();
        $payment = Payment::create([
            'service_order_id' => $order->id, 'payment_method' => 'cash',
            'amount' => $order->grand_total, 'status' => 'waiting_verification',
        ]);

        $this->actingAs($admin)->patch("/payments/{$payment->id}/verify");

        $order->booking->refresh();
        $this->assertContains($order->booking->status, ['paid', 'completed', 'ready_for_delivery']);
    }

    public function test_16_auditlog_tercatat_saat_payment_dibuat(): void
    {
        [$customer, , , $order] = $this->makeOrderWithTotal();

        $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.cash_payment_confirmed']);
    }

    public function test_17_unauthorized_verify_menghasilkan_403(): void
    {
        [, , , $order] = $this->makeOrderWithTotal();
        $payment = Payment::create([
            'service_order_id' => $order->id, 'payment_method' => 'cash',
            'amount' => $order->grand_total, 'status' => 'waiting_verification',
        ]);
        $randomUser = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $response = $this->actingAs($randomUser)->patch("/payments/{$payment->id}/verify");

        $response->assertForbidden();
    }
}
