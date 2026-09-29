<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', AppNotification::class);

        $notifications = AppNotification::where('user_id', auth()->id())->latest()->paginate(20);

        return $this->success('Daftar notifikasi', NotificationResource::collection($notifications));
    }

    public function unread(): JsonResponse
    {
        $this->authorize('viewAny', AppNotification::class);

        $notifications = AppNotification::where('user_id', auth()->id())->unread()->latest()->get();

        return $this->success('Notifikasi belum dibaca', NotificationResource::collection($notifications));
    }

    public function markAsRead(AppNotification $notification): JsonResponse
    {
        $this->authorize('update', $notification);

        $notification->markAsRead();

        return $this->success('Notifikasi ditandai sudah dibaca', new NotificationResource($notification->fresh()));
    }
}
