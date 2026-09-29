<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    /**
     * Entry point /dashboard. Admin/mechanic/courier diarahkan ke dashboard
     * masing-masing (route berbeda, dengan middleware role terpisah).
     * Customer langsung dilayani di sini.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        return match ($user->role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'mechanic' => redirect()->route('mechanic.dashboard'),
            'courier'  => redirect()->route('courier.dashboard'),
            default    => view('dashboard', $this->dashboardService->forCustomer($user)),
        };
    }

    public function admin(): View
    {
        return view('admin.dashboard', $this->dashboardService->forAdmin());
    }

    public function mechanic(): View
    {
        return view('mechanic.dashboard', $this->dashboardService->forMechanic(auth()->user()));
    }

    public function courier(): View
    {
        return view('courier.dashboard', $this->dashboardService->forCourier(auth()->user()));
    }
}
