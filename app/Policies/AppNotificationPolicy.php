<?php

namespace App\Policies;

use App\Models\AppNotification;
use App\Models\User;

class AppNotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AppNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    public function update(User $user, AppNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
