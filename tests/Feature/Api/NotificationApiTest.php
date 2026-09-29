<?php

namespace Tests\Feature\Api;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_isolation_hanya_notifikasi_sendiri(): void
    {
        $user1 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $user2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        AppNotification::create(['user_id' => $user1->id, 'title' => 'Punya U1', 'message' => 'x', 'type' => 'info']);
        AppNotification::create(['user_id' => $user2->id, 'title' => 'Punya U2', 'message' => 'x', 'type' => 'info']);
        $token = $user1->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/notifications');

        $response->assertJsonFragment(['title' => 'Punya U1']);
        $response->assertJsonMissing(['title' => 'Punya U2']);
    }

    public function test_unread_endpoint_hanya_yang_belum_dibaca(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        AppNotification::create(['user_id' => $user->id, 'title' => 'Unread', 'message' => 'x', 'type' => 'info']);
        AppNotification::create(['user_id' => $user->id, 'title' => 'Read', 'message' => 'x', 'type' => 'info', 'read_at' => now()]);
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/notifications/unread');

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Unread']);
    }

    public function test_mark_read_bekerja(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $notification = AppNotification::create(['user_id' => $user->id, 'title' => 'Test', 'message' => 'x', 'type' => 'info']);
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_unauthenticated_menghasilkan_401(): void
    {
        $this->getJson('/api/v1/notifications')->assertStatus(401);
    }

    public function test_tidak_ada_leakage_saat_mark_read_milik_orang_lain(): void
    {
        $user1 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $user2 = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $notification = AppNotification::create(['user_id' => $user1->id, 'title' => 'Private', 'message' => 'x', 'type' => 'info']);
        $token = $user2->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(403);
        $this->assertNull($notification->fresh()->read_at);
    }
}
