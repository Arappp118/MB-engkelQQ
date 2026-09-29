<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceOrder;

class ServiceOrderPolicy
{
    /** Index: semua role authenticated boleh; listFor() sudah scope data per role. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceOrder $order): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isMechanic()) return $order->mechanic_id === $user->id;
        if ($user->isCustomer()) return $order->booking->customer_id === $user->id;
        return false;
    }

    public function update(User $user, ServiceOrder $order): bool
    {
        if ($user->isAdmin()) return true;
        return $user->isMechanic() && $order->mechanic_id === $user->id;
    }

    public function addItem(User $user, ServiceOrder $order): bool
    {
        return $this->update($user, $order);
    }

    public function complete(User $user, ServiceOrder $order): bool
    {
        return $this->update($user, $order);
    }
}
