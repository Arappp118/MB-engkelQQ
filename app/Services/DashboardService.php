<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\DeliveryTask;
use App\Models\Payment;
use App\Models\ServiceItem;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;

class DashboardService
{
    public function forCustomer(User $user): array
    {
        return [
            'vehicles_count' => $user->vehicles()->count(),
            'active_bookings' => $user->bookings()->active()->count(),
            'active_service_orders' => ServiceOrder::whereHas('booking', fn ($q) => $q->where('customer_id', $user->id))
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'pending_payments' => Payment::whereHas('serviceOrder.booking', fn ($q) => $q->where('customer_id', $user->id))
                ->where('status', 'waiting_verification')
                ->count(),
            'recent_bookings' => $user->bookings()->with('vehicle')->latest()->limit(5)->get(),
            'unread_notifications' => $user->appNotifications()->unread()->count(),
            'latest_delivery' => DeliveryTask::whereHas('booking', fn ($q) => $q->where('customer_id', $user->id))
                ->latest()->first(),
        ];
    }

    public function forMechanic(User $user): array
    {
        $orders = $user->serviceOrders()->with('booking.vehicle');

        return [
            'assigned_total' => (clone $orders)->count(),
            'pending' => (clone $orders)->where('status', 'pending')->count(),
            'in_progress' => (clone $orders)->where('status', 'in_progress')->count(),
            'completed' => (clone $orders)->where('status', 'completed')->count(),
            'active_orders' => (clone $orders)->whereIn('status', ['pending', 'in_progress'])->latest()->limit(10)->get(),
            'unread_notifications' => $user->appNotifications()->unread()->count(),
        ];
    }

    public function forCourier(User $user): array
    {
        $tasks = $user->deliveryTasks()->with('booking.vehicle');

        return [
            'pickup_total' => (clone $tasks)->where('type', 'pickup')->count(),
            'delivery_total' => (clone $tasks)->where('type', 'delivery')->count(),
            'active_total' => (clone $tasks)->whereIn('status', ['assigned', 'in_progress'])->count(),
            'completed_total' => (clone $tasks)->where('status', 'completed')->count(),
            'active_tasks' => (clone $tasks)->whereIn('status', ['assigned', 'in_progress'])->latest()->limit(10)->get(),
            'unread_notifications' => $user->appNotifications()->unread()->count(),
        ];
    }

    public function forAdmin(): array
    {
        return [
            'total_customers' => User::where('role', 'customer')->count(),
            'total_vehicles' => Vehicle::count(),
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::active()->count(),
            'total_service_orders' => ServiceOrder::count(),
            'payment_pending' => Payment::where('status', 'waiting_verification')->count(),
            'payment_verified' => Payment::where('status', 'verified')->count(),
            'delivery_task_total' => DeliveryTask::count(),
            'invoice_total' => ServiceOrder::where('status', 'completed')->count(),
            'mechanic_total' => User::where('role', 'mechanic')->count(),
            'courier_total' => User::where('role', 'courier')->count(),
            'service_item_total' => ServiceItem::count(),
            'recent_bookings' => Booking::with(['customer', 'vehicle'])->latest()->limit(5)->get(),
        ];
    }
}
