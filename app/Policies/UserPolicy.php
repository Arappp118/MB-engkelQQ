<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admin dapat melihat daftar user.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin dapat melihat detail user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin dapat membuat akun staff (mechanic/courier).
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin dapat mengupdate data staff; tidak boleh mengubah admin.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() && ! $model->isAdmin();
    }

    /**
     * Admin dapat mengaktifkan/menonaktifkan staff; tidak boleh menonaktifkan admin.
     */
    public function toggleActive(User $user, User $model): bool
    {
        return $user->isAdmin() && ! $model->isAdmin();
    }
}
