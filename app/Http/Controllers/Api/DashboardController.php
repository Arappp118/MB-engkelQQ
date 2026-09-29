<?php

namespace App\Http\Controllers\Api;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    /**
     * Response menyesuaikan role authenticated user (tidak pernah dari
     * request). Reuse penuh DashboardService — tidak duplikasi agregasi.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $data = match ($user->role) {
            'admin'    => $this->forAdmin(),
            'mechanic' => $this->forMechanic($user),
            'courier'  => $this->forCourier($user),
            default    => $this->forCustomer($user),
        };

        return $this->success('Dashboard', $data);
    }

    protected function forCustomer($user): array
    {
        $d = $this->dashboardService->forCustomer($user);

        return [
            'vehicles_count'         => $d['vehicles_count'],
            'active_bookings'        => $d['active_bookings'],
            'active_service_orders'  => $d['active_service_orders'],
            'pending_payments'       => $d['pending_payments'],
            'unread_notifications'   => $d['unread_notifications'],
            'recent_bookings' => $d['recent_bookings']->map(fn ($b) => [
                'id' => $b->id, 'nomor_booking' => $b->nomor_booking,
                'status' => $b->status, 'vehicle' => $b->vehicle->nomor_polisi ?? null,
            ]),
            'latest_delivery' => $d['latest_delivery'] ? [
                'id' => $d['latest_delivery']->id,
                'type' => $d['latest_delivery']->type,
                'status' => $d['latest_delivery']->status,
            ] : null,
        ];
    }

    protected function forMechanic($user): array
    {
        $d = $this->dashboardService->forMechanic($user);

        return [
            'assigned_total'       => $d['assigned_total'],
            'pending'              => $d['pending'],
            'in_progress'          => $d['in_progress'],
            'completed'            => $d['completed'],
            'unread_notifications' => $d['unread_notifications'],
            'active_orders' => $d['active_orders']->map(fn ($o) => [
                'id' => $o->id, 'status' => $o->status,
                'vehicle' => $o->booking->vehicle->nomor_polisi ?? null,
            ]),
        ];
    }

    protected function forCourier($user): array
    {
        $d = $this->dashboardService->forCourier($user);

        return [
            'pickup_total'          => $d['pickup_total'],
            'delivery_total'        => $d['delivery_total'],
            'active_total'          => $d['active_total'],
            'completed_total'       => $d['completed_total'],
            'unread_notifications'  => $d['unread_notifications'],
            'active_tasks' => $d['active_tasks']->map(fn ($t) => [
                'id' => $t->id, 'type' => $t->type, 'status' => $t->status,
                'delivery_fee' => $t->delivery_fee,
            ]),
        ];
    }

    protected function forAdmin(): array
    {
        $d = $this->dashboardService->forAdmin();

        return [
            'total_customers'      => $d['total_customers'],
            'total_vehicles'       => $d['total_vehicles'],
            'total_bookings'       => $d['total_bookings'],
            'active_bookings'      => $d['active_bookings'],
            'total_service_orders' => $d['total_service_orders'],
            'payment_pending'      => $d['payment_pending'],
            'payment_verified'     => $d['payment_verified'],
            'delivery_task_total'  => $d['delivery_task_total'],
            'invoice_total'        => $d['invoice_total'],
            'mechanic_total'       => $d['mechanic_total'],
            'courier_total'        => $d['courier_total'],
            'service_item_total'   => $d['service_item_total'],
            'recent_bookings' => $d['recent_bookings']->map(fn ($b) => [
                'id' => $b->id, 'nomor_booking' => $b->nomor_booking,
                'customer' => $b->customer->name ?? null, 'status' => $b->status,
            ]),
        ];
    }
}
