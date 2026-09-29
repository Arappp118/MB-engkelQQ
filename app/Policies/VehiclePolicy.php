<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['customer', 'admin']);
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin() || $vehicle->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isAdmin();
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin() || $vehicle->user_id === $user->id;
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin() || $vehicle->user_id === $user->id;
    }
}
