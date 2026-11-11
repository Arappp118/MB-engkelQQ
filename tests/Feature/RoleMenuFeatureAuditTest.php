<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoleMenuFeatureAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Create the 4 system actors.
     */
    protected function createActors(): array
    {
        return [
            'admin' => User::factory()->create(['name' => 'Admin Test', 'role' => 'admin', 'is_active' => true]),
            'mechanic' => User::factory()->create([
                'name' => 'Mekanik Test',
                'role' => 'mechanic',
                'is_active' => true,
                'specialization' => 'mekanik_4_tak',
            ]),
            'courier' => User::factory()->create(['name' => 'Kurir Test', 'role' => 'courier', 'is_active' => true]),
            'customer' => User::factory()->create(['name' => 'Customer Test', 'role' => 'customer', 'is_active' => true]),
        ];
    }

    // =========================================================================
    // 1. CUSTOMER ROLE AUDIT
    // =========================================================================

    public function test_customer_can_access_dashboard_and_profile(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        // Dashboard
        $response = $this->actingAs($customer)->get('/dashboard');
        $response->assertOk();

        // Profile edit
        $response = $this->actingAs($customer)->get('/profile');
        $response->assertOk();

        // Profile update
        $response = $this->actingAs($customer)->patch('/profile', [
            'name' => 'Customer Updated',
            'email' => $customer->email,
        ]);
        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'name' => 'Customer Updated']);
    }

    public function test_customer_vehicle_lifecycle_add_edit_delete_and_readd(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        // 1. Access vehicle index & create page
        $this->actingAs($customer)->get('/vehicles')->assertOk();
        $this->actingAs($customer)->get('/vehicles/create')->assertOk();

        // 2. Add vehicle
        $response = $this->actingAs($customer)->post('/vehicles', [
            'nomor_polisi' => 'B 7777 QA',
            'merk' => 'Honda',
            'model' => 'Vario 150',
            'tahun' => 2021,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'matic',
            'warna' => 'Hitam',
        ]);
        $response->assertRedirect('/vehicles');
        $vehicle = Vehicle::where('nomor_polisi', 'B 7777 QA')->first();
        $this->assertNotNull($vehicle);
        $this->assertEquals($customer->id, $vehicle->user_id);

        // 3. Edit vehicle
        $this->actingAs($customer)->get("/vehicles/{$vehicle->id}/edit")->assertOk();
        $response = $this->actingAs($customer)->put("/vehicles/{$vehicle->id}", [
            'nomor_polisi' => 'B 7777 QA',
            'merk' => 'Honda',
            'model' => 'Vario 160 Modif',
            'tahun' => 2022,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'matic',
            'warna' => 'Merah',
        ]);
        $response->assertRedirect('/vehicles');
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'model' => 'Vario 160 Modif',
        ]);

        // 4. Delete vehicle (Soft delete)
        $response = $this->actingAs($customer)->delete("/vehicles/{$vehicle->id}");
        $response->assertRedirect('/vehicles');
        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);

        // 5. RE-ADD SAME LICENSE PLATE (Regression test for User Bug)
        $response = $this->actingAs($customer)->post('/vehicles', [
            'nomor_polisi' => 'B 7777 QA',
            'merk' => 'Honda',
            'model' => 'Vario Baru Re-Added',
            'tahun' => 2023,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'matic',
            'warna' => 'Silver',
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/vehicles');

        // Confirm active vehicle in DB
        $restored = Vehicle::where('nomor_polisi', 'B 7777 QA')->first();
        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at);
        $this->assertEquals('Vario Baru Re-Added', $restored->model);
        $this->assertEquals($customer->id, $restored->user_id);
    }

    public function test_customer_booking_flow_create_view_and_cancel(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B 9999 BK',
            'merk' => 'Yamaha',
            'model' => 'NMAX 155',
            'tahun' => 2022,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'matic',
        ]);

        // 1. Visit booking index & create form
        $this->actingAs($customer)->get('/bookings')->assertOk();
        $this->actingAs($customer)->get('/bookings/create')->assertOk();

        // 2. Store booking
        $response = $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'waktu' => '10:00',
            'jenis_layanan' => 'service_rutin',
            'keluhan' => 'Tarikan gas berat dan oli minta diganti',
            'pickup_requested' => '0',
        ]);
        $response->assertRedirect();
        $booking = Booking::where('vehicle_id', $vehicle->id)->latest('id')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('pending', $booking->status);

        // 3. Show booking
        $this->actingAs($customer)->get("/bookings/{$booking->id}")->assertOk();

        // 4. Cancel booking
        $response = $this->actingAs($customer)->patch("/bookings/{$booking->id}/cancel", [
            'alasan' => 'Jadwal bertabrakan dengan acara keluarga',
        ]);
        $response->assertRedirect();
        $this->assertEquals('cancelled', $booking->fresh()->status);
    }

    public function test_customer_cannot_access_unauthorized_routes(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($customer)->get('/mekanik/dashboard')->assertForbidden();
        $this->actingAs($customer)->get('/kurir/dashboard')->assertForbidden();
        $this->actingAs($customer)->get('/admin/users/customers')->assertForbidden();
        $this->actingAs($customer)->get('/admin/users/mechanics')->assertForbidden();
        $this->actingAs($customer)->get('/admin/payment-settings')->assertForbidden();
    }

    // =========================================================================
    // 2. MECHANIC ROLE AUDIT
    // =========================================================================

    public function test_mechanic_can_view_orders_and_manage_diagnosis_and_items(): void
    {
        $actors = $this->createActors();
        $mechanic = $actors['mechanic'];
        $customer = $actors['customer'];

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B 3333 MEK',
            'merk' => 'Honda',
            'model' => 'Supra X 125',
            'tahun' => 2020,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'manual',
        ]);

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'jenis_layanan' => 'service_rutin',
            'keluhan' => 'Servis berkala',
            'pickup_requested' => false,
            'status' => 'in_service',
        ]);

        $order = ServiceOrder::create([
            'booking_id' => $booking->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'pending',
            'subtotal' => 0,
            'grand_total' => 0,
        ]);

        $itemSparepart = ServiceItem::create([
            'category' => '4_tak',
            'name' => 'Kampas Rem Ori',
            'price' => 50000,
            'unit' => 'set',
            'stock' => 10,
            'is_sparepart' => true,
            'is_active' => true,
        ]);

        // 1. Mechanic dashboard
        $this->actingAs($mechanic)->get('/mekanik/dashboard')->assertOk();

        // 2. Mechanic service orders list & show
        $this->actingAs($mechanic)->get('/service-orders')->assertOk();
        $this->actingAs($mechanic)->get("/service-orders/{$order->id}")->assertOk();

        // 3. Start service
        $response = $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/start");
        $response->assertRedirect();
        $this->assertEquals('in_progress', $order->fresh()->status);

        // 4. Save diagnosis
        $response = $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/diagnosis", [
            'diagnosis_mechanic' => 'Kampas rem tipis dan aus parah, oli mesin kotor',
            'notes' => 'Perlu penggantian kampas rem depan',
        ]);
        $response->assertRedirect();
        $this->assertEquals('Kampas rem tipis dan aus parah, oli mesin kotor', $order->fresh()->diagnosis_mechanic);

        // 5. Add sparepart item
        $response = $this->actingAs($mechanic)->post("/service-orders/{$order->id}/items", [
            'service_item_id' => $itemSparepart->id,
            'quantity' => 1,
        ]);
        $response->assertRedirect();
        $this->assertEquals(9, $itemSparepart->fresh()->stock);
        $this->assertEquals(50000, $order->fresh()->grand_total);

        // 6. Complete service
        $response = $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/complete");
        $response->assertRedirect();
        $this->assertEquals('completed', $order->fresh()->status);

        // 7. Mechanic can view active service items catalog
        $this->actingAs($mechanic)->get('/service-items')->assertOk();
    }

    public function test_mechanic_cannot_access_other_mechanic_order_or_admin_areas(): void
    {
        $actors = $this->createActors();
        $mechanic = $actors['mechanic'];
        $customer = $actors['customer'];
        $otherMechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B 4444 OTH',
            'merk' => 'Yamaha',
            'model' => 'Mio',
            'tahun' => 2019,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'matic',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'jenis_layanan' => 'service_rutin',
            'keluhan' => 'Servis',
            'pickup_requested' => false,
            'status' => 'confirmed',
        ]);
        $order = ServiceOrder::create([
            'booking_id' => $booking->id,
            'mechanic_id' => $otherMechanic->id,
            'status' => 'pending',
            'subtotal' => 0,
            'grand_total' => 0,
        ]);

        // Cannot view or update other mechanic's order
        $this->actingAs($mechanic)->get("/service-orders/{$order->id}")->assertForbidden();
        $this->actingAs($mechanic)->patch("/service-orders/{$order->id}/start")->assertForbidden();

        // Cannot access admin dashboard or users management
        $this->actingAs($mechanic)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($mechanic)->get('/admin/users/mechanics')->assertForbidden();
    }

    // =========================================================================
    // 3. COURIER ROLE AUDIT
    // =========================================================================

    public function test_courier_delivery_task_flow(): void
    {
        $actors = $this->createActors();
        $courier = $actors['courier'];
        $customer = $actors['customer'];

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B 5555 KUR',
            'merk' => 'Suzuki',
            'model' => 'Satria FU',
            'tahun' => 2018,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'manual',
        ]);
        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '10:00',
            'jenis_layanan' => 'service_rutin',
            'keluhan' => 'Pickup jemput motor',
            'pickup_requested' => true,
            'alamat_pickup' => 'Jl. Kebon Jeruk No 12',
            'status' => 'waiting_pickup',
        ]);
        $task = DeliveryTask::create([
            'booking_id' => $booking->id,
            'courier_id' => $courier->id,
            'type' => 'pickup',
            'address' => 'Jl. Kebon Jeruk No 12',
            'status' => 'assigned',
        ]);

        // 1. Courier dashboard
        $this->actingAs($courier)->get('/kurir/dashboard')->assertOk();

        // 2. Delivery tasks index & show
        $this->actingAs($courier)->get('/delivery-tasks')->assertOk();
        $this->actingAs($courier)->get("/delivery-tasks/{$task->id}")->assertOk();

        // 3. Start delivery task
        $response = $this->actingAs($courier)->patch("/delivery-tasks/{$task->id}/start");
        $response->assertRedirect();
        $this->assertEquals('in_progress', $task->fresh()->status);

        // 4. Complete delivery task
        $response = $this->actingAs($courier)->patch("/delivery-tasks/{$task->id}/complete", [
            'notes' => 'Motor sudah berhasil diambil dari lokasi customer.',
        ]);
        $response->assertRedirect();
        $this->assertEquals('completed', $task->fresh()->status);

        // 5. Courier can view delivery settings
        $this->actingAs($courier)->get('/delivery-settings')->assertOk();
    }

    public function test_courier_cannot_access_admin_or_mechanic_areas(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'is_active' => true]);

        $this->actingAs($courier)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($courier)->get('/mekanik/dashboard')->assertForbidden();
        $this->actingAs($courier)->get('/admin/users/couriers')->assertForbidden();
        $this->actingAs($courier)->patch('/delivery-settings', [
            'base_fare' => 10000,
        ])->assertForbidden();
    }

    // =========================================================================
    // 4. ADMIN ROLE AUDIT
    // =========================================================================

    public function test_admin_staff_management_mechanic_and_courier(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // 1. View customers
        $this->actingAs($admin)->get('/admin/users/customers')->assertOk();

        // 2. Manage mechanics
        $this->actingAs($admin)->get('/admin/users/mechanics')->assertOk();
        $this->actingAs($admin)->get('/admin/users/mechanics/create')->assertOk();

        // Create mechanic
        $response = $this->actingAs($admin)->post('/admin/users/mechanics', [
            'name' => 'Mekanik Baru',
            'email' => 'mekanik.baru@motocare.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '081234567890',
            'address' => 'Jl. Bengkel No 1',
            'specialization' => 'mekanik_4_tak',
        ]);
        $response->assertRedirect('/admin/users/mechanics');
        $mech = User::where('email', 'mekanik.baru@motocare.test')->first();
        $this->assertNotNull($mech);
        $this->assertEquals('mechanic', $mech->role);
        $this->assertTrue((bool)$mech->is_active);

        // Edit mechanic
        $this->actingAs($admin)->get("/admin/users/mechanics/{$mech->id}/edit")->assertOk();
        $response = $this->actingAs($admin)->patch("/admin/users/mechanics/{$mech->id}", [
            'name' => 'Mekanik Baru Updated',
            'email' => 'mekanik.baru@motocare.test',
            'phone' => '081234567899',
            'address' => 'Jl. Bengkel Baru',
            'specialization' => 'mekanik_kelistrikan',
        ]);
        $response->assertRedirect('/admin/users/mechanics');
        $this->assertEquals('Mekanik Baru Updated', $mech->fresh()->name);
        $this->assertEquals('mekanik_kelistrikan', $mech->fresh()->specialization);

        // Toggle mechanic active status
        $this->actingAs($admin)->patch("/admin/users/mechanics/{$mech->id}/toggle");
        $this->assertFalse((bool)$mech->fresh()->is_active);
        $this->actingAs($admin)->patch("/admin/users/mechanics/{$mech->id}/toggle");
        $this->assertTrue((bool)$mech->fresh()->is_active);

        // 3. Manage couriers
        $this->actingAs($admin)->get('/admin/users/couriers')->assertOk();
        $this->actingAs($admin)->get('/admin/users/couriers/create')->assertOk();

        // Create courier
        $response = $this->actingAs($admin)->post('/admin/users/couriers', [
            'name' => 'Kurir Express',
            'email' => 'kurir.express@motocare.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '0899999999',
            'address' => 'Jl. Kurir No 5',
        ]);
        $response->assertRedirect('/admin/users/couriers');
        $courier = User::where('email', 'kurir.express@motocare.test')->first();
        $this->assertNotNull($courier);
        $this->assertEquals('courier', $courier->role);

        // Toggle courier active status
        $this->actingAs($admin)->patch("/admin/users/couriers/{$courier->id}/toggle");
        $this->assertFalse((bool)$courier->fresh()->is_active);
    }

    public function test_admin_manage_service_items_and_spareparts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // 1. Visit index & create form
        $this->actingAs($admin)->get('/service-items')->assertOk();
        $this->actingAs($admin)->get('/service-items/create')->assertOk();

        // 2. Store sparepart item
        $response = $this->actingAs($admin)->post('/service-items', [
            'name' => 'Busi Iridium Racing',
            'category' => 'kelistrikan',
            'description' => 'Busi pengapian super fokus',
            'price' => 75000,
            'unit' => 'pcs',
            'is_sparepart' => true,
            'stock' => 20,
        ]);
        $response->assertRedirect();
        $item = ServiceItem::where('name', 'Busi Iridium Racing')->first();
        $this->assertNotNull($item);
        $this->assertEquals(20, $item->stock);

        // 3. Update stock
        $response = $this->actingAs($admin)->patch("/service-items/{$item->id}/stock", [
            'stock' => 35,
        ]);
        $response->assertRedirect();
        $this->assertEquals(35, $item->fresh()->stock);

        // 4. Deactivate item
        $response = $this->actingAs($admin)->delete("/service-items/{$item->id}");
        $response->assertRedirect('/service-items');
        $this->assertFalse((bool)$item->fresh()->is_active);
    }

    public function test_admin_payment_verification_and_rejection(): void
    {
        $actors = $this->createActors();
        $admin = $actors['admin'];
        $customer = $actors['customer'];
        $mechanic = $actors['mechanic'];

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'nomor_polisi' => 'B 6666 PAY',
            'merk' => 'Honda',
            'model' => 'Beat',
            'tahun' => 2021,
            'tipe_mesin' => '4_tak',
            'transmisi' => 'matic',
        ]);

        $booking = Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'jenis_layanan' => 'service_rutin',
            'keluhan' => 'Bayar tagihan',
            'pickup_requested' => false,
            'status' => 'service_completed',
        ]);

        $order = ServiceOrder::create([
            'booking_id' => $booking->id,
            'mechanic_id' => $mechanic->id,
            'subtotal' => 150000,
            'grand_total' => 150000,
            'status' => 'completed',
        ]);

        $file = UploadedFile::fake()->image('bukti_transfer.jpg');
        $payment = Payment::create([
            'service_order_id' => $order->id,
            'amount' => 150000,
            'payment_method' => 'transfer',
            'proof_path' => $file->store('payments', 'public'),
            'status' => 'waiting_verification',
        ]);

        // 1. Admin payments list & detail
        $this->actingAs($admin)->get('/payments')->assertOk();
        $this->actingAs($admin)->get("/payments/{$payment->id}")->assertOk();

        // 2. Reject payment
        $response = $this->actingAs($admin)->patch("/payments/{$payment->id}/reject", [
            'reason' => 'Bukti buram dan tidak terbaca jelas',
        ]);
        $response->assertRedirect();
        $this->assertEquals('rejected', $payment->fresh()->status);
        $this->assertEquals('Bukti buram dan tidak terbaca jelas', $payment->fresh()->notes);

        // 3. Customer resubmits payment with valid proof
        $file2 = UploadedFile::fake()->image('bukti_transfer_jelas.jpg');
        $response = $this->actingAs($customer)->post("/service-orders/{$order->id}/payments", [
            'payment_method' => 'transfer',
            'proof' => $file2,
        ]);
        $response->assertRedirect();
        $newPayment = Payment::where('service_order_id', $order->id)->latest('id')->first();
        $this->assertNotNull($newPayment);
        $this->assertEquals('waiting_verification', $newPayment->status);

        // 4. Admin verifies the new payment
        $response = $this->actingAs($admin)->patch("/payments/{$newPayment->id}/verify");
        $response->assertRedirect();
        $this->assertEquals('verified', $newPayment->fresh()->status);
        $this->assertNotNull($newPayment->fresh()->paid_at);
        $this->assertEquals('completed', $booking->fresh()->status);
    }

    public function test_admin_manage_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // 1. Payment settings
        $this->actingAs($admin)->get('/admin/payment-settings')->assertOk();
        $response = $this->actingAs($admin)->patch('/admin/payment-settings', [
            'bank_name' => 'BCA',
            'bank_account_name' => 'PT MOTOCARE INDONESIA',
            'bank_account_number' => '1234567890',
            'qris_merchant_name' => 'MotoCare Official',
            'payment_instructions' => 'Transfer tepat sesuai nominal grand total.',
        ]);
        $response->assertRedirect('/admin/payment-settings');
        $this->assertEquals('BCA', PaymentSetting::get('bank_name'));

        // 2. Delivery settings
        $this->actingAs($admin)->get('/delivery-settings')->assertOk();
        $response = $this->actingAs($admin)->patch('/delivery-settings', [
            'price_per_km' => 5000,
        ]);
        $response->assertRedirect('/delivery-settings');
        $this->assertEquals('5000', (string)DeliverySetting::get('price_per_km'));
    }
}
