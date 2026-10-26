<?php

namespace Tests\Feature\Api;

use App\Models\AuditLog;
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
            'user_id' => $customer->id, 'nomor_polisi' => 'B'.rand(1000, 9999).'PA',
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

    public function test_pembayaran_ditolak_jika_service_order_belum_completed(): void
    {
        [$customer, $order] = $this->makeOrder();
        $order->update(['status' => 'in_progress']);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", [
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['service_order']);
    }

    public function test_pembayaran_ditolak_jika_grand_total_nol(): void
    {
        [$customer, $order] = $this->makeOrder(0);
        $order->update(['grand_total' => 0]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", [
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['service_order']);
    }

    public function test_pembayaran_ditolak_jika_sudah_ada_pembayaran_aktif(): void
    {
        [$customer, $order] = $this->makeOrder();
        Payment::create([
            'service_order_id' => $order->id,
            'payment_method' => 'transfer',
            'amount' => $order->grand_total,
            'status' => 'waiting_verification',
        ]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", [
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['service_order']);
    }

    public function test_verifikasi_pembayaran_atomik_dan_menolak_eksekusi_ganda(): void
    {
        [, $order] = $this->makeOrder();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;
        $payment = Payment::create([
            'service_order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => $order->grand_total,
            'status' => 'waiting_verification',
        ]);

        // Verifikasi pertama sukses
        $res1 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/payments/{$payment->id}/verify");
        $res1->assertOk();

        // Verifikasi kedua ditolak 422
        $res2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/payments/{$payment->id}/verify");
        $res2->assertStatus(422);

        // Audit log verified hanya 1 kali
        $this->assertEquals(1, AuditLog::where('action', 'payment.verified')->where('entity_id', $payment->id)->count());
    }

    public function test_paid_at_null_saat_submit_dan_terisi_saat_verify(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", ['payment_method' => 'cash']);

        $res->assertStatus(201);
        $res->assertJsonPath('data.paid_at', null);

        $paymentId = $res->json('data.id');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $adminToken = $admin->createToken('t')->plainTextToken;

        $this->app['auth']->forgetGuards();

        $verifyRes = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/v1/payments/{$paymentId}/verify");

        $verifyRes->assertOk();
        $this->assertNotNull($verifyRes->json('data.paid_at'));
        $this->assertEquals('verified', $verifyRes->json('data.status'));
    }

    public function test_unauthorized_reject_ditolak_403(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;
        $payment = Payment::create([
            'service_order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => $order->grand_total,
            'status' => 'waiting_verification',
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/payments/{$payment->id}/reject", ['reason' => 'invalid'])
            ->assertStatus(403);
    }

    public function test_mechanic_dan_courier_tidak_dapat_melihat_detail_payment_di_api(): void
    {
        [$customer, $order] = $this->makeOrder();
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);
        $payment = Payment::create([
            'service_order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => $order->grand_total,
            'status' => 'waiting_verification',
        ]);

        $mToken = $mechanic->createToken('t')->plainTextToken;
        $cToken = $courier->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$mToken}")
            ->getJson("/api/v1/payments/{$payment->id}")
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$cToken}")
            ->getJson("/api/v1/payments/{$payment->id}")
            ->assertStatus(403);
    }

    public function test_customer_dapat_mengajukan_ulang_setelah_pembayaran_ditolak_di_api(): void
    {
        [$customer, $order] = $this->makeOrder();
        $token = $customer->createToken('t')->plainTextToken;
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $adminToken = $admin->createToken('t')->plainTextToken;

        $payment1 = Payment::create([
            'service_order_id' => $order->id,
            'payment_method' => 'transfer',
            'amount' => $order->grand_total,
            'status' => 'waiting_verification',
        ]);

        // Admin tolak pembayaran pertama
        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/v1/payments/{$payment1->id}/reject", ['reason' => 'Bukti buram'])
            ->assertOk();

        $this->app['auth']->forgetGuards();

        // Customer ajukan pembayaran baru
        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/service-orders/{$order->id}/payments", [
                'payment_method' => 'cash',
                'notes' => 'Bayar tunai di kasir',
            ]);

        $res->assertStatus(201);
        $this->assertEquals('waiting_verification', $res->json('data.status'));
        $this->assertEquals(2, Payment::where('service_order_id', $order->id)->count());
    }
}
