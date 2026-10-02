<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    // ── Specialization labels ──────────────────────────────────────────────────

    public const SPECIALIZATIONS = [
        'mekanik_2_tak'       => '2 Tak',
        'mekanik_4_tak'       => '4 Tak',
        'mekanik_kelistrikan' => 'Kelistrikan',
    ];

    // ── Customers (read-only) ─────────────────────────────────────────────────

    public function customers(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $customers = User::where('role', 'customer')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.customers', compact('customers'));
    }

    // ── Mechanics ─────────────────────────────────────────────────────────────

    public function mechanics(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $mechanics = User::where('role', 'mechanic')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.mechanics.index', [
            'mechanics'       => $mechanics,
            'specializations' => self::SPECIALIZATIONS,
        ]);
    }

    public function createMechanic(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.mechanics.create', [
            'specializations' => self::SPECIALIZATIONS,
        ]);
    }

    public function storeMechanic(StaffRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->safe()->only(['name', 'email', 'phone', 'address', 'specialization']);
        $data['role']      = 'mechanic';
        $data['is_active'] = true;
        $data['password']  = Hash::make($request->validated('password'));

        User::create($data);

        return redirect()
            ->route('admin.users.mechanics')
            ->with('success', 'Akun mekanik berhasil dibuat.');
    }

    public function editMechanic(User $user): View
    {
        $this->authorize('update', $user);
        abort_unless($user->isMechanic(), 404);

        return view('admin.users.mechanics.edit', [
            'user'            => $user,
            'specializations' => self::SPECIALIZATIONS,
        ]);
    }

    public function updateMechanic(StaffRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        abort_unless($user->isMechanic(), 404);

        $data = $request->safe()->only(['name', 'email', 'phone', 'address', 'specialization']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.mechanics')
            ->with('success', 'Data mekanik berhasil diperbarui.');
    }

    public function toggleMechanic(User $user): RedirectResponse
    {
        $this->authorize('toggleActive', $user);
        abort_unless($user->isMechanic(), 404);

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.users.mechanics')
            ->with('success', "Mekanik {$user->name} berhasil {$status}.");
    }

    // ── Couriers ──────────────────────────────────────────────────────────────

    public function couriers(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $couriers = User::where('role', 'courier')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.couriers.index', compact('couriers'));
    }

    public function createCourier(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.couriers.create');
    }

    public function storeCourier(StaffRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->safe()->only(['name', 'email', 'phone', 'address']);
        $data['role']      = 'courier';
        $data['is_active'] = true;
        $data['password']  = Hash::make($request->validated('password'));

        User::create($data);

        return redirect()
            ->route('admin.users.couriers')
            ->with('success', 'Akun kurir berhasil dibuat.');
    }

    public function editCourier(User $user): View
    {
        $this->authorize('update', $user);
        abort_unless($user->isCourier(), 404);

        return view('admin.users.couriers.edit', compact('user'));
    }

    public function updateCourier(StaffRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        abort_unless($user->isCourier(), 404);

        $data = $request->safe()->only(['name', 'email', 'phone', 'address']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.couriers')
            ->with('success', 'Data kurir berhasil diperbarui.');
    }

    public function toggleCourier(User $user): RedirectResponse
    {
        $this->authorize('toggleActive', $user);
        abort_unless($user->isCourier(), 404);

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.users.couriers')
            ->with('success', "Kurir {$user->name} berhasil {$status}.");
    }
}
