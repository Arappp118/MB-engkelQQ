<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isCustomer()) return $booking->customer_id === $user->id;
        if ($user->isMechanic()) return $booking->serviceOrder?->mechanic_id === $user->id;
        if ($user->isCourier()) return $booking->deliveryTasks()->where('courier_id', $user->id)->exists();
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isAdmin();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) return true;
        return $user->isCustomer() && $booking->customer_id === $user->id
            && in_array($booking->status, ['pending', 'confirmed']);
    }

    public function confirm(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }
}
