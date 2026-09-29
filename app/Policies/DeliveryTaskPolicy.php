<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeliveryTask;

class DeliveryTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DeliveryTask $task): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isCourier()) return $task->courier_id === $user->id;
        if ($user->isCustomer()) return $task->booking->customer_id === $user->id;
        return false;
    }

    /**
     * Hanya admin yang boleh assign courier ke task.
     */
    public function assign(User $user): bool
    {
        return $user->isAdmin();
    }

    public function accept(User $user): bool
    {
        return $user->isCourier() || $user->isAdmin();
    }

    public function complete(User $user, DeliveryTask $task): bool
    {
        if ($user->isAdmin()) return true;
        return $user->isCourier() && $task->courier_id === $user->id;
    }
}
