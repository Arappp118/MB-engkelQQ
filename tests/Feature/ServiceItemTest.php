<?php

namespace Tests\Feature;

use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceItemTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUsers(): array
    {
        return [
            User::factory()->create(['role' => 'admin', 'is_active' => true]),
            User::factory()->create(['role' => 'mechanic', 'is_active' => true, 'specialization' => 'mekanik_4_tak']),
            User::factory()->create(['role' => 'customer', 'is_active' => true]),
            User::factory()->create(['role' => 'courier', 'is_active' => true]),
        ];
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'category' => '4_tak',
            'name' => 'Ganti Oli Mesin',
            'price' => 50000,
            'unit' => 'liter',
            'is_sparepart' => false,
            'is_active' => true,
        ], $overrides);
    }

    public function test_admin_dapat_membuat_service_item(): void
    {
        [$admin] = $this->makeUsers();

        $response = $this->actingAs($admin)->post('/service-items', $this->validPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('service_items', [
            'name' => 'Ganti Oli Mesin',
            'price' => 50000,
        ]);
    }

    public function test_admin_dapat_mengubah_harga(): void
    {
        [$admin] = $this->makeUsers();
        $item = ServiceItem::create($this->validPayload());

        $response = $this->actingAs($admin)->put("/service-items/{$item->id}", $this->validPayload(['price' => 75000]));

        $response->assertRedirect();
        $this->assertDatabaseHas('service_items', [
            'id' => $item->id,
            'price' => 75000,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'service_item.price_changed',
            'entity_id' => $item->id,
        ]);
    }

    public function test_mechanic_tidak_dapat_mengubah_harga(): void
    {
        [, $mechanic] = $this->makeUsers();
        $item = ServiceItem::create($this->validPayload());

        $response = $this->actingAs($mechanic)->put("/service-items/{$item->id}", $this->validPayload(['price' => 999999]));

        $response->assertForbidden();
        $this->assertDatabaseHas('service_items', [
            'id' => $item->id,
            'price' => 50000,
        ]);
    }

    public function test_customer_tidak_dapat_mengubah_harga(): void
    {
        [, , $customer] = $this->makeUsers();
        $item = ServiceItem::create($this->validPayload());

        $response = $this->actingAs($customer)->put("/service-items/{$item->id}", $this->validPayload(['price' => 1]));

        $response->assertForbidden();
        $this->assertDatabaseHas('service_items', [
            'id' => $item->id,
            'price' => 50000,
        ]);
    }

    public function test_mechanic_dapat_membaca_item_aktif(): void
    {
        [, $mechanic] = $this->makeUsers();
        ServiceItem::create($this->validPayload());

        $response = $this->actingAs($mechanic)->get('/service-items');

        $response->assertStatus(200);
        $response->assertViewIs('service-items.index');
    }

    public function test_item_nonaktif_tidak_dapat_digunakan_untuk_transaksi(): void
    {
        $item = ServiceItem::create($this->validPayload(['is_active' => false]));

        $this->assertFalse(ServiceItem::active()->where('id', $item->id)->exists());
    }

    public function test_harga_tidak_dapat_dimanipulasi_melalui_request_mechanic(): void
    {
        [, $mechanic] = $this->makeUsers();

        // Mechanic coba create item baru dengan harga sendiri -> harus 403 total (create pun ditolak)
        $response = $this->actingAs($mechanic)->post('/service-items', $this->validPayload(['price' => 1]));

        $response->assertForbidden();
        $this->assertDatabaseMissing('service_items', ['price' => 1]);
    }

    public function test_sparepart_dapat_dibuat_dan_dikelola_admin(): void
    {
        [$admin] = $this->makeUsers();

        $response = $this->actingAs($admin)->post('/service-items', $this->validPayload([
            'name' => 'Kampas Rem Depan',
            'is_sparepart' => true,
            'stock' => 20,
            'category' => 'sparepart',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('service_items', [
            'name' => 'Kampas Rem Depan',
            'is_sparepart' => true,
            'stock' => 20,
        ]);
    }

    public function test_stok_divalidasi_wajib_untuk_sparepart(): void
    {
        [$admin] = $this->makeUsers();

        $response = $this->actingAs($admin)->post('/service-items', $this->validPayload([
            'name' => 'Kampas Rem Belakang',
            'is_sparepart' => true,
            'stock' => null,
            'category' => 'sparepart',
        ]));

        $response->assertSessionHasErrors('stock');
    }

    public function test_unauthorized_access_menghasilkan_403(): void
    {
        [, , $customer] = $this->makeUsers();

        $response = $this->actingAs($customer)->get('/service-items/create');

        $response->assertForbidden();
    }

    public function test_courier_tidak_dapat_membuat_item(): void
    {
        [, , , $courier] = $this->makeUsers();

        $response = $this->actingAs($courier)->post('/service-items', $this->validPayload());

        $response->assertForbidden();
    }

    public function test_auditlog_tercatat_saat_item_dibuat(): void
    {
        [$admin] = $this->makeUsers();

        $this->actingAs($admin)->post('/service-items', $this->validPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'service_item.created',
        ]);
    }

    public function test_auditlog_tercatat_saat_stok_diubah(): void
    {
        [$admin] = $this->makeUsers();
        $item = ServiceItem::create($this->validPayload(['is_sparepart' => true, 'stock' => 10, 'category' => 'sparepart']));

        $this->actingAs($admin)->patch("/service-items/{$item->id}/stock", ['stock' => 25]);

        $this->assertDatabaseHas('service_items', ['id' => $item->id, 'stock' => 25]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'service_item.stock_changed',
            'entity_id' => $item->id,
        ]);
    }

    public function test_admin_dapat_menonaktifkan_item(): void
    {
        [$admin] = $this->makeUsers();
        $item = ServiceItem::create($this->validPayload());

        $response = $this->actingAs($admin)->delete("/service-items/{$item->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('service_items', ['id' => $item->id, 'is_active' => false]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'service_item.deactivated',
        ]);
    }
}
