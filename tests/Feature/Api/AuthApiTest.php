<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_berhasil(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_login_valid_berhasil(): void
    {
        $user = User::factory()->create([
            'email' => 'test@motocare.test',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@motocare.test',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'message', 'data' => ['user', 'token']]);
    }

    public function test_login_password_salah_menghasilkan_401(): void
    {
        User::factory()->create([
            'email' => 'test2@motocare.test',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test2@motocare.test',
            'password' => 'salah',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_email_tidak_ada_menghasilkan_401(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'tidakada@motocare.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_validation_gagal_menghasilkan_422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'bukan-email',
        ]);

        $response->assertStatus(422);
    }

    public function test_token_berhasil_dibuat(): void
    {
        $user = User::factory()->create([
            'email' => 'test3@motocare.test',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test3@motocare.test',
            'password' => 'password123',
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_me_dengan_token_berhasil(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->id);
    }

    public function test_me_tanpa_token_menghasilkan_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_logout_dengan_token_berhasil(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $response->assertOk();
    }

    public function test_token_setelah_logout_tidak_dapat_digunakan(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/auth/logout');
        $logoutResponse->assertOk();

        // Verifikasi langsung ke database (bukan HTTP call kedua dalam proses
        // yang sama, karena guard sanctum di-cache dalam satu siklus test).
        // Ini adalah mekanisme validasi asli yang dipakai Sanctum di request nyata.
        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

        $this->assertNull($accessToken);
    }

    public function test_logout_tidak_menghapus_token_user_lain(): void
    {
        $user1 = User::factory()->create(['is_active' => true]);
        $user2 = User::factory()->create(['is_active' => true]);
        $token1 = $user1->createToken('test')->plainTextToken;
        $token2 = $user2->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token1}")->postJson('/api/v1/auth/logout');

        $response = $this->withHeader('Authorization', "Bearer {$token2}")->getJson('/api/v1/auth/me');

        $response->assertOk();
    }

    public function test_user_tidak_dapat_menentukan_role_sendiri(): void
    {
        $user = User::factory()->create([
            'email' => 'test4@motocare.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test4@motocare.test',
            'password' => 'password123',
            'role' => 'admin', // percobaan manipulasi
        ]);

        $response->assertJsonPath('data.user.role', 'customer'); // tetap role asli
    }

    public function test_password_tidak_pernah_muncul_di_response(): void
    {
        $user = User::factory()->create([
            'email' => 'test5@motocare.test',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test5@motocare.test',
            'password' => 'password123',
        ]);

        $response->assertJsonMissingPath('data.user.password');
    }

    public function test_remember_token_tidak_muncul_di_response(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/auth/me');

        $response->assertJsonMissingPath('data.remember_token');
    }

    public function test_user_hanya_mendapatkan_data_dirinya_sendiri(): void
    {
        $user1 = User::factory()->create(['is_active' => true]);
        $user2 = User::factory()->create(['is_active' => true]);
        $token1 = $user1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token1}")->getJson('/api/v1/auth/me');

        $response->assertJsonPath('data.id', $user1->id);
        $response->assertJsonMissing(['id' => $user2->id]);
    }

    public function test_rate_limit_login_bekerja(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'ratelimit@motocare.test',
                'password' => 'salah',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ratelimit@motocare.test',
            'password' => 'salah',
        ]);

        $response->assertStatus(429);
    }
}
