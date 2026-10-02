<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function admin(): User
    {
        return User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);
    }

    private function customer(): User
    {
        return User::factory()->create([
            'role'      => 'customer',
            'is_active' => true,
        ]);
    }

    private function mechanic(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role'           => 'mechanic',
            'specialization' => 'mekanik_4_tak',
            'is_active'      => true,
        ], $attrs));
    }

    private function courier(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role'      => 'courier',
            'is_active' => true,
        ], $attrs));
    }

    // ── Guest / non-admin forbidden ───────────────────────────────────────────

    /** @test */
    public function guest_cannot_access_user_management(): void
    {
        $this->get(route('admin.users.customers'))->assertRedirect(route('login'));
        $this->get(route('admin.users.mechanics'))->assertRedirect(route('login'));
        $this->get(route('admin.users.couriers'))->assertRedirect(route('login'));
    }

    /** @test */
    public function customer_cannot_access_user_management(): void
    {
        $this->actingAs($this->customer());

        $this->get(route('admin.users.customers'))->assertForbidden();
        $this->get(route('admin.users.mechanics'))->assertForbidden();
        $this->get(route('admin.users.couriers'))->assertForbidden();
    }

    /** @test */
    public function mechanic_cannot_access_user_management(): void
    {
        $this->actingAs($this->mechanic());

        $this->get(route('admin.users.mechanics'))->assertForbidden();
        $this->get(route('admin.users.couriers'))->assertForbidden();
    }

    /** @test */
    public function courier_cannot_access_user_management(): void
    {
        $this->actingAs($this->courier());

        $this->get(route('admin.users.mechanics'))->assertForbidden();
        $this->get(route('admin.users.couriers'))->assertForbidden();
    }

    // ── Admin: View Customers ─────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_customers_list(): void
    {
        $admin    = $this->admin();
        $customer = $this->customer();

        $this->actingAs($admin)
            ->get(route('admin.users.customers'))
            ->assertOk()
            ->assertSee($customer->name);
    }

    // ── Admin: View Mechanics ─────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_mechanics_list(): void
    {
        $admin    = $this->admin();
        $mechanic = $this->mechanic();

        $this->actingAs($admin)
            ->get(route('admin.users.mechanics'))
            ->assertOk()
            ->assertSee($mechanic->name);
    }

    // ── Admin: Create Mechanic ────────────────────────────────────────────────

    /** @test */
    public function admin_can_see_create_mechanic_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.users.mechanics.create'))
            ->assertOk()
            ->assertSee('Spesialisasi');
    }

    /** @test */
    public function admin_can_create_mechanic_with_specialization(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.mechanics.store'), [
                'name'                  => 'Mekanik Baru',
                'email'                 => 'mekanikbaru@test.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'specialization'        => 'mekanik_2_tak',
                'phone'                 => '081234567890',
                'address'               => 'Jl. Test No. 1',
            ])
            ->assertRedirect(route('admin.users.mechanics'));

        $user = User::where('email', 'mekanikbaru@test.com')->firstOrFail();
        $this->assertSame('mechanic', $user->role);
        $this->assertSame('mekanik_2_tak', $user->specialization);
        $this->assertTrue($user->is_active);

        // Password harus di-hash, bukan plaintext
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotSame('password123', $user->password);
    }

    /** @test */
    public function create_mechanic_requires_specialization(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.mechanics.store'), [
                'name'                  => 'Mekanik Tanpa Spesialis',
                'email'                 => 'nospek@test.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                // specialization tidak diisi
            ])
            ->assertSessionHasErrors('specialization');
    }

    /** @test */
    public function create_mechanic_rejects_invalid_specialization(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.mechanics.store'), [
                'name'                  => 'Mekanik Bad Spek',
                'email'                 => 'badspek@test.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'specialization'        => 'invalid_value',
            ])
            ->assertSessionHasErrors('specialization');
    }

    // ── Admin: Edit Mechanic ──────────────────────────────────────────────────

    /** @test */
    public function admin_can_edit_mechanic(): void
    {
        $admin    = $this->admin();
        $mechanic = $this->mechanic(['specialization' => 'mekanik_4_tak']);

        $this->actingAs($admin)
            ->patch(route('admin.users.mechanics.update', $mechanic), [
                'name'           => 'Mekanik Updated',
                'email'          => $mechanic->email,
                'specialization' => 'mekanik_kelistrikan',
            ])
            ->assertRedirect(route('admin.users.mechanics'));

        $mechanic->refresh();
        $this->assertSame('Mekanik Updated', $mechanic->name);
        $this->assertSame('mekanik_kelistrikan', $mechanic->specialization);
        // Role tidak berubah
        $this->assertSame('mechanic', $mechanic->role);
    }

    /** @test */
    public function edit_mechanic_updates_password_when_provided(): void
    {
        $admin    = $this->admin();
        $mechanic = $this->mechanic();

        $this->actingAs($admin)
            ->patch(route('admin.users.mechanics.update', $mechanic), [
                'name'                  => $mechanic->name,
                'email'                 => $mechanic->email,
                'specialization'        => $mechanic->specialization,
                'password'              => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $mechanic->refresh();
        $this->assertTrue(Hash::check('newpassword123', $mechanic->password));
    }

    /** @test */
    public function edit_mechanic_keeps_password_when_blank(): void
    {
        $admin    = $this->admin();
        $mechanic = $this->mechanic();
        $oldHash  = $mechanic->password;

        $this->actingAs($admin)
            ->patch(route('admin.users.mechanics.update', $mechanic), [
                'name'           => $mechanic->name,
                'email'          => $mechanic->email,
                'specialization' => $mechanic->specialization,
                // password kosong
            ]);

        $mechanic->refresh();
        $this->assertSame($oldHash, $mechanic->password);
    }

    // ── Admin: Toggle Mechanic ────────────────────────────────────────────────

    /** @test */
    public function admin_can_deactivate_mechanic(): void
    {
        $admin    = $this->admin();
        $mechanic = $this->mechanic(['is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.users.mechanics.toggle', $mechanic))
            ->assertRedirect(route('admin.users.mechanics'));

        $this->assertFalse($mechanic->fresh()->is_active);
    }

    /** @test */
    public function admin_can_reactivate_mechanic(): void
    {
        $admin    = $this->admin();
        $mechanic = $this->mechanic(['is_active' => false]);

        $this->actingAs($admin)
            ->patch(route('admin.users.mechanics.toggle', $mechanic))
            ->assertRedirect(route('admin.users.mechanics'));

        $this->assertTrue($mechanic->fresh()->is_active);
    }

    /** @test */
    public function inactive_mechanic_is_rejected_at_login(): void
    {
        $mechanic = $this->mechanic(['is_active' => false, 'password' => Hash::make('password')]);

        $this->post(route('login'), [
            'email'    => $mechanic->email,
            'password' => 'password',
        ]);

        // RoleMiddleware ログアウト after detecting is_active=false
        // User should not remain authenticated after hitting any protected route
        $this->actingAs($mechanic)
            ->get(route('mechanic.dashboard'))
            ->assertRedirect(route('login'));
    }

    // ── Admin: Couriers ───────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_couriers_list(): void
    {
        $admin   = $this->admin();
        $courier = $this->courier();

        $this->actingAs($admin)
            ->get(route('admin.users.couriers'))
            ->assertOk()
            ->assertSee($courier->name);
    }

    /** @test */
    public function admin_can_create_courier(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.couriers.store'), [
                'name'                  => 'Kurir Baru',
                'email'                 => 'kurirbaru@test.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.users.couriers'));

        $user = User::where('email', 'kurirbaru@test.com')->firstOrFail();
        $this->assertSame('courier', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function admin_can_edit_courier(): void
    {
        $admin   = $this->admin();
        $courier = $this->courier();

        $this->actingAs($admin)
            ->patch(route('admin.users.couriers.update', $courier), [
                'name'  => 'Kurir Updated',
                'email' => $courier->email,
            ])
            ->assertRedirect(route('admin.users.couriers'));

        $courier->refresh();
        $this->assertSame('Kurir Updated', $courier->name);
        $this->assertSame('courier', $courier->role);
    }

    /** @test */
    public function admin_can_toggle_courier_status(): void
    {
        $admin   = $this->admin();
        $courier = $this->courier(['is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.users.couriers.toggle', $courier))
            ->assertRedirect(route('admin.users.couriers'));

        $this->assertFalse($courier->fresh()->is_active);
    }

    // ── Security: role cannot be changed via forms ────────────────────────────

    /** @test */
    public function updating_mechanic_does_not_change_role(): void
    {
        $admin    = $this->admin();
        $mechanic = $this->mechanic();

        // Coba kirim role=admin lewat body form (bypass UI)
        $this->actingAs($admin)
            ->patch(route('admin.users.mechanics.update', $mechanic), [
                'name'           => $mechanic->name,
                'email'          => $mechanic->email,
                'specialization' => $mechanic->specialization,
                'role'           => 'admin', // forsed input, harus diabaikan
            ]);

        $this->assertSame('mechanic', $mechanic->fresh()->role);
    }

    /** @test */
    public function admin_cannot_toggle_their_own_role_account(): void
    {
        $admin = $this->admin();

        // Admin mencoba menonaktifkan akun mekanik yang sebenarnya adalah admin
        // Buat user dengan role admin kedua untuk test forbidden
        $anotherAdmin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.users.mechanics.toggle', $anotherAdmin))
            ->assertForbidden();
    }

    // ── Customer cannot create staff accounts ─────────────────────────────────

    /** @test */
    public function customer_cannot_create_mechanic(): void
    {
        $this->actingAs($this->customer())
            ->post(route('admin.users.mechanics.store'), [
                'name'                  => 'Hack Mechanic',
                'email'                 => 'hack@test.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'specialization'        => 'mekanik_4_tak',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'hack@test.com']);
    }

    /** @test */
    public function customer_cannot_create_courier(): void
    {
        $this->actingAs($this->customer())
            ->post(route('admin.users.couriers.store'), [
                'name'                  => 'Hack Courier',
                'email'                 => 'hackcourier@test.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'hackcourier@test.com']);
    }
}
