<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeVehicleFor(User $user, array $overrides = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'user_id' => $user->id,
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'VA',
            'merk' => 'Honda', 'model' => 'Vario', 'tahun' => 2022,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ], $overrides));
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nomor_polisi' => 'B' . rand(1000, 9999) . 'NW',
            'merk' => 'Yamaha', 'model' => 'NMAX', 'tahun' => 2023,
            'tipe_mesin' => '4_tak', 'transmisi' => 'matic',
        ], $overrides);
    }

    public function test_1_unauthenticated_get_vehicles_menghasilkan_401(): void
    {
        $response = $this->getJson('/api/v1/vehicles');

        $response->assertStatus(401);
    }

    public function test_2_customer_dapat_list_kendaraan_miliknya(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;
        $this->makeVehicleFor($customer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/vehicles');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_3_customer_tidak_melihat_kendaraan_customer_lain_di_list(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customer2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer1->createToken('t')->plainTextToken;
        $this->makeVehicleFor($customer2);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/vehicles');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_4_customer_dapat_membuat_kendaraan(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/vehicles', $this->validPayload());

        $response->assertStatus(201);
        $this->assertDatabaseHas('vehicles', [
            'user_id' => $customer->id,
            'merk' => 'Yamaha',
        ]);
    }

    public function test_5_user_id_selalu_dari_authenticated_user(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/vehicles', $this->validPayload());

        $response->assertJsonPath('data.user_id', $customer->id);
    }

    public function test_6_request_tidak_dapat_memanipulasi_user_id(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customerLain = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/vehicles', $this->validPayload(['user_id' => $customerLain->id]));

        $response->assertJsonPath('data.user_id', $customer->id); // bukan customerLain
        $this->assertDatabaseMissing('vehicles', ['user_id' => $customerLain->id]);
    }

    public function test_7_customer_dapat_melihat_kendaraan_miliknya(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;
        $vehicle = $this->makeVehicleFor($customer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/vehicles/{$vehicle->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $vehicle->id);
    }

    public function test_8_customer_tidak_dapat_melihat_kendaraan_user_lain(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customer2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer1->createToken('t')->plainTextToken;
        $vehicle = $this->makeVehicleFor($customer2);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/vehicles/{$vehicle->id}");

        $response->assertStatus(403);
    }

    public function test_9_customer_dapat_update_kendaraan_miliknya(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;
        $vehicle = $this->makeVehicleFor($customer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/vehicles/{$vehicle->id}", $this->validPayload(['merk' => 'Suzuki']));

        $response->assertOk();
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'merk' => 'Suzuki']);
    }

    public function test_10_customer_tidak_dapat_update_kendaraan_user_lain(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customer2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer1->createToken('t')->plainTextToken;
        $vehicle = $this->makeVehicleFor($customer2);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/vehicles/{$vehicle->id}", $this->validPayload());

        $response->assertStatus(403);
    }

    public function test_11_customer_dapat_delete_kendaraan_tanpa_active_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;
        $vehicle = $this->makeVehicleFor($customer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/vehicles/{$vehicle->id}");

        $response->assertOk();
        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
    }

    public function test_12_delete_kendaraan_dengan_active_booking_ditolak(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;
        $vehicle = $this->makeVehicleFor($customer);
        Booking::create([
            'nomor_booking' => Booking::generateNomorBooking(),
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'tanggal' => now()->addDay(), 'waktu' => '10:00',
            'keluhan' => 'Test', 'jenis_layanan' => 'perbaikan', 'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/vehicles/{$vehicle->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'deleted_at' => null]);
    }

    public function test_13_validation_invalid_vehicle_ditolak(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/vehicles', ['merk' => 'Honda']); // field wajib lain kosong

        $response->assertStatus(422);
    }

    public function test_14_unauthenticated_post_menghasilkan_401(): void
    {
        $response = $this->postJson('/api/v1/vehicles', $this->validPayload());

        $response->assertStatus(401);
    }

    public function test_15_unauthenticated_put_menghasilkan_401(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = $this->makeVehicleFor($customer);

        $response = $this->putJson("/api/v1/vehicles/{$vehicle->id}", $this->validPayload());

        $response->assertStatus(401);
    }

    public function test_16_unauthenticated_delete_menghasilkan_401(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $vehicle = $this->makeVehicleFor($customer);

        $response = $this->deleteJson("/api/v1/vehicles/{$vehicle->id}");

        $response->assertStatus(401);
    }

    public function test_17_authorization_berjalan_melalui_policy_untuk_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $admin->createToken('t')->plainTextToken;
        $vehicle = $this->makeVehicleFor($customer);

        // Admin boleh lihat kendaraan siapapun (sesuai VehiclePolicy existing)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/vehicles/{$vehicle->id}");

        $response->assertOk();
    }

    public function test_18_response_tidak_membocorkan_data_sensitif(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;
        $this->makeVehicleFor($customer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/vehicles');

        $response->assertJsonMissingPath('data.0.deleted_at');
    }

    public function test_19_get_single_vehicle_idor_customer_a_ke_customer_b(): void
    {
        $customerA = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customerB = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $tokenA = $customerA->createToken('t')->plainTextToken;
        $vehicleB = $this->makeVehicleFor($customerB);

        // IDOR test: GET
        $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->getJson("/api/v1/vehicles/{$vehicleB->id}")->assertStatus(403);

        // IDOR test: PUT
        $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->putJson("/api/v1/vehicles/{$vehicleB->id}", $this->validPayload())->assertStatus(403);

        // IDOR test: DELETE
        $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->deleteJson("/api/v1/vehicles/{$vehicleB->id}")->assertStatus(403);
    }

    public function test_20_api_tidak_menerima_ownership_dari_request_saat_update(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customerLain = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $token = $customer->createToken('t')->plainTextToken;
        $vehicle = $this->makeVehicleFor($customer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/vehicles/{$vehicle->id}", $this->validPayload(['user_id' => $customerLain->id]));

        $response->assertOk();
        $vehicle->refresh();
        $this->assertEquals($customer->id, $vehicle->user_id); // tidak berubah
    }
}
