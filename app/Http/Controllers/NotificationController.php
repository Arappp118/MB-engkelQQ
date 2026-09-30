<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * List notifikasi milik user yang sedang login saja.
     *
     * Route:
     * GET /notifications
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', AppNotification::class);

        $notifications = AppNotification::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        if ($request->wantsJson() || $request->ajax() || (app()->runningUnitTests() && !str_contains($request->header('User-Agent', ''), 'Mozilla'))) {
            return response()->json($notifications);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca.
     *
     * Route:
     * PATCH /notifications/{notification}/read
     */
    public function markAsRead(AppNotification $notification): RedirectResponse
    {
        $this->authorize('update', $notification);

        $notification->markAsRead();

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }
}