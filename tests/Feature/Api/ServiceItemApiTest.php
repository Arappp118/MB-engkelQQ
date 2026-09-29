<?php

namespace Tests\Feature\Api;

use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceItemApiTest extends TestCase
{
    use RefreshDatabase;

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'category' => '4_tak', 'name' => 'Ganti Oli', 'price' => 50000,
            'unit' => 'liter', 'is_sparepart' => false, 'is_active' => true,
        ], $overrides);
    }

    public function test_unauthenticated_menghasilkan_401(): void
    {
        $this->getJson('/api/v1/service-items')->assertStatus(401);
    }

    public function test_admin_dapat_crud_service_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/service-items', $this->payload());
        $response->assertStatus(201);
        $id = $response->json('data.id');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/service-items/{$id}", $this->payload(['price' => 75000]))
            ->assertOk();

        $this->assertDatabaseHas('service_items', ['id' => $id, 'price' => 75000]);
    }

    public function test_non_admin_menghasilkan_403_saat_create(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/service-items', $this->payload())
            ->assertStatus(403);
    }

    public function test_mechanic_tidak_dapat_manipulasi_price(): void
    {
        $item = ServiceItem::create($this->payload());
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $token = $mechanic->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/service-items/{$item->id}", $this->payload(['price' => 1]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('service_items', ['id' => $item->id, 'price' => 50000]);
    }

    public function test_price_snapshot_field_tidak_diterima_untuk_ubah_harga(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;
        $item = ServiceItem::create($this->payload());

        // price_snapshot/unit_price/subtotal/grand_total bukan field valid di sini,
        // hanya 'price' yang dipakai — kirim field asing tidak berpengaruh.
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/service-items/{$item->id}", $this->payload([
                'price' => 60000,
                'price_snapshot' => 1,
                'unit_price' => 1,
            ]));

        $response->assertOk();
        $this->assertDatabaseHas('service_items', ['id' => $item->id, 'price' => 60000]);
    }

    public function test_admin_melihat_item_nonaktif(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $adminToken = $admin->createToken('t')->plainTextToken;
        ServiceItem::create($this->payload(['is_active' => false]));

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->getJson('/api/v1/service-items')->assertJsonCount(1, 'data');
    }

    public function test_mechanic_tidak_melihat_item_nonaktif(): void
    {
        $mechanic = User::factory()->create(['role' => 'mechanic', 'is_active' => true]);
        $mechanicToken = $mechanic->createToken('t')->plainTextToken;
        ServiceItem::create($this->payload(['is_active' => false]));

        $this->withHeader('Authorization', "Bearer {$mechanicToken}")
            ->getJson('/api/v1/service-items')->assertJsonCount(0, 'data');
    }

    public function test_stok_dapat_diupdate_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;
        $item = ServiceItem::create($this->payload(['is_sparepart' => true, 'stock' => 10, 'category' => 'sparepart']));

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/service-items/{$item->id}/stock", ['stock' => 25])
            ->assertOk();

        $this->assertDatabaseHas('service_items', ['id' => $item->id, 'stock' => 25]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'service_item.stock_changed']);
    }

    public function test_admin_dapat_menonaktifkan_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;
        $item = ServiceItem::create($this->payload());

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/service-items/{$item->id}")
            ->assertOk();

        $this->assertDatabaseHas('service_items', ['id' => $item->id, 'is_active' => false]);
    }
}
