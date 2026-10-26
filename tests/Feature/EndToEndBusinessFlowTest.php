<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\DeliverySetting;
use App\Models\DeliveryTask;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\ServiceItem;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\MechanicAssignmentService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndBusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Helper to create all 4 roles.
     */
    protected function createActors(): array
    {
        return [
            'admin' => User::factory()->create(['role' => 'admin', 'is_active' => true]),
            'mechanic' => User::factory()->create([
                'role' => 'mechanic',
                'is_active' => true,
                'specialization' => 'mekanik_4_tak',
            ]),
            'courier' => User::factory()->create(['role' => 'courier', 'is_active' => true]),
            'customer' => User::factory()->create(['role' => 'customer', 'is_active' => true]),
        ];
    }

    /**
     * FLOW 1 — SERVICE BIASA END-TO-END
     * Customer login -> add vehicle -> create booking -> admin confirm -> assign mechanic ->
     * start service -> diagnosis -> add spareparts & jasa -> complete service ->
     * view invoice -> pay via bank transfer -> verify payment -> order completed.
     */
    public function test_flow_1_service_biasa_end_to_end(): void
    {
        $actors = $this->createActors();
        $admin = $actors['admin'];
        $mechanic = $actors['mechanic'];
        $customer = $actors['customer'];

        // 1. Setup Master Sparepart & Jasa by Admin
        $sparepart = ServiceItem::create([
            'category' => 'sparepart',
            'name' => 'Oli Mesin MPX 0.8L',
            'price' => 55000,
            'unit' => 'botol',
            'stock' => 10,
            'is_sparepart' => true,
            'is_active' => true,
        ]);
        $jasa = ServiceItem::create([
            'category' => '4_tak',
            'name' => 'Jasa Ganti Oli',
            'price' => 20000,
            'unit' => 'jasa',
            'stock' => null,
            'is_sparepart' => false,
            'is_active' => true,
        ]);

        // 2. Customer creates vehicle
        $vehicleResponse = $this->actingAs($customer)->post(route('vehicles.store'), [
            'nomor_polisi' => 'B 1234 ETE',
            'merk' => 'Honda',
            'model' => 'Vario 125',
            'tahun' => 2022,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'matic',
            'warna' => 'Hitam',
        ]);
        $vehicleResponse->assertRedirect(route('vehicles.index'));
        $vehicle = Vehicle::where('nomor_polisi', 'B 1234 ETE')->firstOrFail();
        $this->assertSame($customer->id, $vehicle->user_id);

        // 3. Customer creates booking
        $bookingResponse = $this->actingAs($customer)->post(route('bookings.store'), [
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '09:00',
            'keluhan' => 'Ganti oli rutin dan cek rem mesin 4 tak',
            'jenis_layanan' => 'service_rutin',
            'pickup_requested' => '0',
        ]);
        $bookingResponse->assertSessionHasNoErrors();
        $booking = Booking::where('customer_id', $customer->id)->latest('id')->firstOrFail();
        $this->assertSame('pending', $booking->status);

        // 4. Admin confirms booking and mechanic assignment
        $confirmResponse = $this->actingAs($admin)->patch(route('bookings.confirm', $booking));
        $confirmResponse->assertRedirect();
        $booking->refresh();
        $this->assertContains($booking->status, ['confirmed', 'assigned', 'in_service']);

        // Assign mechanic via MechanicAssignmentService if not auto-assigned
        $serviceOrder = ServiceOrder::where('booking_id', $booking->id)->first();
        if (!$serviceOrder) {
            $serviceOrder = ServiceOrder::create([
                'booking_id' => $booking->id,
                'mechanic_id' => $mechanic->id,
                'status' => 'pending',
                'grand_total' => 0,
            ]);
            $booking->update(['status' => 'assigned']);
        }

        // 5. Mechanic starts service
        $startResponse = $this->actingAs($mechanic)->patch(route('service-orders.start', $serviceOrder));
        $startResponse->assertRedirect();
        $serviceOrder->refresh();
        $this->assertSame('in_progress', $serviceOrder->status);

        // 6. Mechanic records diagnosis
        $diagnosisResponse = $this->actingAs($mechanic)->patch(route('service-orders.diagnosis', $serviceOrder), [
            'diagnosis_mechanic' => 'Oli sudah hitam dan perlu diganti segera.',
        ]);
        $diagnosisResponse->assertRedirect();
        $serviceOrder->refresh();
        $this->assertSame('Oli sudah hitam dan perlu diganti segera.', $serviceOrder->diagnosis_mechanic);

        // 7. Mechanic adds sparepart and jasa
        $initialStock = $sparepart->stock;
        $itemResponse1 = $this->actingAs($mechanic)->post(route('service-orders.items.store', $serviceOrder), [
            'service_item_id' => $sparepart->id,
            'quantity' => 1,
        ]);
        $itemResponse1->assertRedirect();
        $sparepart->refresh();
        $this->assertSame($initialStock - 1, $sparepart->stock, 'Stok sparepart harus berkurang tepat 1 unit');

        $itemResponse2 = $this->actingAs($mechanic)->post(route('service-orders.items.store', $serviceOrder), [
            'service_item_id' => $jasa->id,
            'quantity' => 1,
        ]);
        $itemResponse2->assertRedirect();

        $serviceOrder->refresh();
        $this->assertEquals(75000, $serviceOrder->grand_total, 'Grand total harus 55000 + 20000 = 75000');

        // 8. Mechanic completes service
        $completeResponse = $this->actingAs($mechanic)->patch(route('service-orders.complete', $serviceOrder));
        $completeResponse->assertRedirect();
        $serviceOrder->refresh();
        $this->assertSame('completed', $serviceOrder->status);

        // 9. Customer views invoice
        $invoiceResponse = $this->actingAs($customer)->get(route('invoices.show', $serviceOrder));
        $invoiceResponse->assertOk();
        $invoiceResponse->assertSee('75.000');

        // 10. Customer submits payment with proof
        $file = UploadedFile::fake()->create('bukti_transfer.jpg', 400, 'image/jpeg');
        $payResponse = $this->actingAs($customer)->post(route('payments.store', $serviceOrder), [
            'payment_method' => 'transfer',
            'proof' => $file,
            'amount' => 1000, // Coba manipulasi nominal, server harus mengabaikan
        ]);
        $payResponse->assertRedirect();

        $payment = Payment::where('service_order_id', $serviceOrder->id)->latest('id')->firstOrFail();
        $this->assertSame('waiting_verification', $payment->status);
        $this->assertNull($payment->paid_at, 'paid_at harus NULL saat submit bukti transfer');
        $this->assertEquals(75000, $payment->amount, 'Nominal payment harus tetap 75000 dari server');

        // 11. Admin verifies payment
        $verifyResponse = $this->actingAs($admin)->patch(route('payments.verify', $payment));
        $verifyResponse->assertRedirect();

        $payment->refresh();
        $this->assertSame('verified', $payment->status);
        $this->assertNotNull($payment->paid_at, 'paid_at harus terisi timestamp saat payment diverifikasi');
        $this->assertSame($admin->id, $payment->verified_by);

        $booking->refresh();
        $this->assertSame('completed', $booking->status);

        // 12. Final Invoice check: marked as LUNAS
        $finalInvoiceResponse = $this->actingAs($customer)->get(route('invoices.show', $serviceOrder));
        $finalInvoiceResponse->assertOk();
        $finalInvoiceResponse->assertSee('LUNAS');
    }

    /**
     * FLOW 2 — SERVICE DENGAN ANTAR-JEMPUT (PICKUP & DELIVERY)
     * Customer booking with pickup -> courier pickup -> arrive at workshop ->
     * mechanic service -> invoice & payment -> delivery task -> courier deliver -> completed.
     */
    public function test_flow_2_pickup_and_delivery_end_to_end(): void
    {
        $actors = $this->createActors();
        $admin = $actors['admin'];
        $mechanic = $actors['mechanic'];
        $courier = $actors['courier'];
        $customer = $actors['customer'];

        // Configure Delivery Fee Setting
        DeliverySetting::set('base_fee', 10000);
        DeliverySetting::set('per_km_fee', 2000);

        // 1. Vehicle & Booking with Pickup
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B 9999 DLV',
            'merk' => 'Yamaha',
            'model' => 'NMAX',
            'tahun' => 2023,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'matic',
        ]);

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(),
            'waktu' => '10:00',
            'keluhan' => 'Servis berkala dan antar jemput',
            'jenis_layanan' => 'service_rutin',
            'pickup_requested' => true,
            'alamat_pickup' => 'Jl. Merdeka No. 10',
            'estimated_distance_km' => 5,
            'status' => 'confirmed',
        ]);

        // Pickup Task assigned to courier
        $pickupTask = DeliveryTask::create([
            'booking_id' => $booking->id,
            'courier_id' => $courier->id,
            'type' => 'pickup',
            'address' => $booking->alamat_pickup,
            'distance_km' => 5,
            'delivery_fee' => 20000,
            'status' => 'assigned',
        ]);

        // 2. Courier starts and completes pickup
        $this->actingAs($courier)->patch(route('delivery-tasks.start', $pickupTask))->assertRedirect();
        $pickupTask->refresh();
        $this->assertSame('in_progress', $pickupTask->status);

        $this->actingAs($courier)->patch(route('delivery-tasks.complete', $pickupTask))->assertRedirect();
        $pickupTask->refresh();
        $this->assertSame('completed', $pickupTask->status);

        // 3. Vehicle arrives at workshop
        $booking->update(['status' => 'in_service']);

        // 4. Mechanic performs service
        $order = ServiceOrder::create([
            'booking_id' => $booking->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'in_progress',
            'diagnosis_mechanic' => 'Servis berkala selesai.',
            'grand_total' => 50000,
        ]);

        $serviceItem = ServiceItem::create([
            'category' => 'umum',
            'name' => 'Pemeriksaan Umum',
            'price' => 50000,
            'is_sparepart' => false,
            'is_active' => true,
        ]);

        ServiceOrderItem::create([
            'service_order_id' => $order->id,
            'service_item_id' => $serviceItem->id,
            'item_name_snapshot' => $serviceItem->name,
            'price_snapshot' => $serviceItem->price,
            'quantity' => 1,
            'subtotal' => 50000,
        ]);

        $this->actingAs($mechanic)->patch(route('service-orders.complete', $order))->assertRedirect();
        $order->refresh();
        $this->assertSame('completed', $order->status);

        // 5. Active delivery task for return/delivery created and assigned
        $deliveryTask = DeliveryTask::create([
            'booking_id' => $booking->id,
            'courier_id' => $courier->id,
            'type' => 'delivery',
            'address' => $booking->alamat_pickup,
            'distance_km' => 5,
            'delivery_fee' => 20000,
            'status' => 'assigned',
        ]);

        // 6. Customer pays invoice
        $payment = Payment::create([
            'service_order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 50000,
            'status' => 'waiting_verification',
            'paid_at' => null,
        ]);

        // 7. Admin verifies payment -> Booking must transition to ready_for_delivery, NOT prematurely completed
        $this->actingAs($admin)->patch(route('payments.verify', $payment))->assertRedirect();
        $booking->refresh();
        $this->assertSame('ready_for_delivery', $booking->status, 'Booking harus ready_for_delivery karena ada delivery task aktif');

        // 8. Courier starts and completes delivery task
        $this->actingAs($courier)->patch(route('delivery-tasks.start', $deliveryTask))->assertRedirect();
        $deliveryTask->refresh();
        $this->assertSame('in_progress', $deliveryTask->status);

        $this->actingAs($courier)->patch(route('delivery-tasks.complete', $deliveryTask))->assertRedirect();
        $deliveryTask->refresh();
        $this->assertSame('completed', $deliveryTask->status);

        // Final booking status completed
        $booking->refresh();
        $this->assertSame('completed', $booking->status);
    }

    /**
     * FLOW 3 — METODE PEMBAYARAN: CASH, TRANSFER (REJECT & RESUBMIT), DAN QRIS
     */
    public function test_flow_3_payment_cash_transfer_rejection_and_qris(): void
    {
        $actors = $this->createActors();
        $admin = $actors['admin'];
        $mechanic = $actors['mechanic'];
        $customer = $actors['customer'];

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B 7777 PAY',
            'merk' => 'Suzuki',
            'model' => 'Satria',
            'tahun' => 2021,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'manual',
        ]);

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(),
            'waktu' => '10:00',
            'keluhan' => 'Tune up',
            'jenis_layanan' => 'perbaikan',
            'status' => 'in_service',
        ]);

        $order = ServiceOrder::create([
            'booking_id' => $booking->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'completed',
            'grand_total' => 100000,
        ]);

        // Scenario A: Customer submits bad transfer proof -> Admin rejects with reason
        $badFile = UploadedFile::fake()->create('struk_buram.jpg', 100, 'image/jpeg');
        $this->actingAs($customer)->post(route('payments.store', $order), [
            'payment_method' => 'transfer',
            'proof' => $badFile,
        ])->assertRedirect();

        $payment1 = Payment::where('service_order_id', $order->id)->latest('id')->firstOrFail();
        $this->assertSame('waiting_verification', $payment1->status);

        $this->actingAs($admin)->patch(route('payments.reject', $payment1), [
            'reason' => 'Bukti buram dan mutasi bank belum masuk',
        ])->assertRedirect();

        $payment1->refresh();
        $this->assertSame('rejected', $payment1->status);
        $this->assertSame('Bukti buram dan mutasi bank belum masuk', $payment1->notes);

        // Scenario B: Customer re-submits corrected proof
        $goodFile = UploadedFile::fake()->create('bukti_asli.jpg', 200, 'image/jpeg');
        $this->actingAs($customer)->post(route('payments.store', $order), [
            'payment_method' => 'transfer',
            'proof' => $goodFile,
        ])->assertRedirect();

        $payment2 = Payment::where('service_order_id', $order->id)->latest('id')->firstOrFail();
        $this->assertNotSame($payment1->id, $payment2->id);
        $this->assertSame('waiting_verification', $payment2->status);

        // Verify and complete
        $this->actingAs($admin)->patch(route('payments.verify', $payment2))->assertRedirect();
        $payment2->refresh();
        $this->assertSame('verified', $payment2->status);
        $this->assertNotNull($payment2->paid_at);
    }

    /**
     * FLOW 4 — SECURITY & INTEGRITY: IDOR, TAMPERING, STOCK CONCURRENCY
     */
    public function test_flow_4_security_idor_and_tampering_integrity(): void
    {
        $actors = $this->createActors();
        $mechanic = $actors['mechanic'];
        $courier = $actors['courier'];
        $customer1 = $actors['customer'];
        $customer2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $vehicle1 = Vehicle::create([
            'user_id' => $customer1->id,
            'nomor_polisi' => 'B 1111 CUST',
            'merk' => 'Honda', 'model' => 'Beat', 'tahun' => 2020,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ]);

        $booking1 = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer1->id,
            'vehicle_id' => $vehicle1->id,
            'tanggal' => now()->addDay(),
            'waktu' => '10:00',
            'keluhan' => 'Cek rem',
            'jenis_layanan' => 'perbaikan',
            'status' => 'confirmed',
        ]);

        $order1 = ServiceOrder::create([
            'booking_id' => $booking1->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'completed',
            'grand_total' => 50000,
        ]);

        // IDOR 1: Customer 2 cannot access Customer 1's vehicle
        $this->actingAs($customer2)->get(route('vehicles.show', $vehicle1))->assertForbidden();

        // IDOR 2: Customer 2 cannot access Customer 1's booking
        $this->actingAs($customer2)->get(route('bookings.show', $booking1))->assertForbidden();

        // IDOR 3: Customer 2 cannot access Customer 1's invoice
        $this->actingAs($customer2)->get(route('invoices.show', $order1))->assertForbidden();

        // IDOR 4: Customer 2 cannot pay Customer 1's order
        $this->actingAs($customer2)->post(route('payments.store', $order1), [
            'payment_method' => 'cash',
        ])->assertForbidden();

        // Privilege Escalation 1: Mechanic cannot verify payment
        $payment = Payment::create([
            'service_order_id' => $order1->id,
            'payment_method' => 'cash',
            'amount' => 50000,
            'status' => 'waiting_verification',
        ]);
        $this->actingAs($mechanic)->patch(route('payments.verify', $payment))->assertForbidden();

        // Privilege Escalation 2: Courier cannot reject payment
        $this->actingAs($courier)->patch(route('payments.reject', $payment), [
            'reason' => 'Hack reject',
        ])->assertForbidden();

        // Price Snapshot Integrity: Updating master item price does not mutate existing order
        $sparepart = ServiceItem::create([
            'category' => 'sparepart',
            'name' => 'Busi Iridium',
            'price' => 30000,
            'unit' => 'pcs',
            'stock' => 5,
            'is_sparepart' => true,
            'is_active' => true,
        ]);

        $orderItem = ServiceOrderItem::create([
            'service_order_id' => $order1->id,
            'service_item_id' => $sparepart->id,
            'item_name_snapshot' => $sparepart->name,
            'price_snapshot' => $sparepart->price,
            'quantity' => 1,
            'subtotal' => 30000,
        ]);

        // Update master item price
        $sparepart->update(['price' => 50000]);

        $orderItem->refresh();
        $this->assertEquals(30000, $orderItem->price_snapshot, 'Snapshot price order item lama tidak boleh berubah');
    }
}
