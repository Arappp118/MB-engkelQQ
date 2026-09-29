<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    public function notify(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $entityType = null,
        ?int $entityId = null
    ): void {
        AppNotification::create([
            'user_id'     => $user->id,
            'title'       => $title,
            'message'     => $message,
            'type'        => $type,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
        ]);
    }

    public function notifyMany(array $users, string $title, string $message, string $type = 'info'): void
    {
        foreach ($users as $user) {
            $this->notify($user, $title, $message, $type);
        }
    }
}
