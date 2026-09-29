<?php

namespace App\Policies;

use App\Models\ServiceItem;
use App\Models\User;

class ServiceItemPolicy
{
    /**
     * Semua role login boleh melihat daftar (mekanik butuh pilih item aktif,
     * customer lihat harga di invoice, admin kelola semua).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceItem $item): bool
    {
        return true;
    }

    /**
     * Hanya admin yang boleh membuat master jasa/sparepart.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang boleh mengubah data master, termasuk harga.
     */
    public function update(User $user, ServiceItem $item): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang boleh menonaktifkan/menghapus.
     */
    public function delete(User $user, ServiceItem $item): bool
    {
        return $user->isAdmin();
    }
}
