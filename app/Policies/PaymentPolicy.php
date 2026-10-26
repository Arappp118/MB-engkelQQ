<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $payment->serviceOrder?->booking?->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isAdmin();
    }

    public function verify(User $user, ?Payment $payment = null): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, ?Payment $payment = null): bool
    {
        return $user->isAdmin();
    }
}
