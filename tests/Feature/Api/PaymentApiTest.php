<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeOrder(float $grandTotal = 200000): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'nomor_polisi' => 'B' . rand(1000, 9999) . 'PA',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'x', 'jenis_layanan' => 'perbaikan', 'status' => 'completed',
        ]);
        $order = ServiceOrder::create(['booking_id' => $booking->id, 'mechanic_id' => $mechanic->id, 'status' => 'completed', 'grand_total' => $grandTotal]);

        return [$customer, $order];
    }

    public function test_amount_selalu_dari_grand_total_server(): void
    {
        [$customer, $order] = $this->makeOrder(250000);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", [
                'payment_method' => 'cash',
                'amount' => 1, // percobaan manipulasi
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.amount', 250000);
    }

    public function test_status_tidak_dapat_dimanipulasi(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", [
                'payment_method' => 'cash',
                'status' => 'paid',
            ]);

        $response->assertJsonPath('data.status', 'waiting_verification');
    }

    public function test_customer_hanya_dapat_bayar_order_miliknya(): void
    {
        [, $order] = $this->makeOrder();
        $customerLain = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customerLain->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", ['payment_method' => 'cash'])
            ->assertStatus(403);
    }

    public function test_proof_upload_aman(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;
        $file = UploadedFile::fake()->create('bukti.exe', 100);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", [
                'payment_method' => 'transfer',
                'proof' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_admin_dapat_verify(): void
    {
        [, $order] = $this->makeOrder();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;
        $payment = Payment::create(['service_order_id' => $order->id, 'payment_method' => 'cash', 'amount' => $order->grand_total, 'status' => 'waiting_verification']);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/payments/{$payment->id}/verify")
            ->assertOk();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'verified']);
    }

    public function test_non_admin_tidak_dapat_verify(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;
        $payment = Payment::create(['service_order_id' => $order->id, 'payment_method' => 'cash', 'amount' => $order->grand_total, 'status' => 'waiting_verification']);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/payments/{$payment->id}/verify")
            ->assertStatus(403);
    }

    public function test_admin_dapat_reject(): void
    {
        [, $order] = $this->makeOrder();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;
        $payment = Payment::create(['service_order_id' => $order->id, 'payment_method' => 'transfer', 'amount' => $order->grand_total, 'status' => 'waiting_verification']);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/payments/{$payment->id}/reject", ['reason' => 'Bukti tidak valid'])
            ->assertOk();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'rejected']);
    }

    public function test_duplicate_verify_ditolak(): void
    {
        [, $order] = $this->makeOrder();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;
        $payment = Payment::create(['service_order_id' => $order->id, 'payment_method' => 'cash', 'amount' => $order->grand_total, 'status' => 'verified']);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/payments/{$payment->id}/verify");

        $response->assertStatus(422);
    }

    public function test_auditlog_tercatat(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", ['payment_method' => 'cash']);

        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.created']);
    }

    public function test_notification_terkirim_saat_verified(): void
    {
        [$customer, $order] = $this->makeOrder();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;
        $payment = Payment::create(['service_order_id' => $order->id, 'payment_method' => 'cash', 'amount' => $order->grand_total, 'status' => 'waiting_verification']);

        $this->withHeader('Authorization', "Bearer {$token}")->postJson("/api/v1/payments/{$payment->id}/verify");

        $this->assertDatabaseHas('notifications', ['user_id' => $customer->id, 'title' => 'Pembayaran Diverifikasi']);
    }

    public function test_response_tidak_expose_proof_path(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", ['payment_method' => 'cash']);

        $response->assertJsonMissingPath('data.proof_path');
    }
}
