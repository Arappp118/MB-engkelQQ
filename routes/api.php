<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeliverySettingController;
use App\Http\Controllers\Api\DeliveryTaskController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ServiceItemController;
use App\Http\Controllers\Api\ServiceOrderController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class);

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('vehicles', VehicleController::class)->names('api.vehicles');

        Route::apiResource('bookings', BookingController::class)
            ->only(['index', 'store', 'show'])
            ->names('api.bookings');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('api.bookings.cancel');
        Route::post('bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('api.bookings.confirm');

        Route::get('service-orders', [ServiceOrderController::class, 'index'])->name('api.service-orders.index');
        Route::get('service-orders/{service_order}', [ServiceOrderController::class, 'show'])->name('api.service-orders.show');
        Route::post('service-orders/{service_order}/start', [ServiceOrderController::class, 'start'])->name('api.service-orders.start');
        Route::post('service-orders/{service_order}/diagnosis', [ServiceOrderController::class, 'diagnosis'])->name('api.service-orders.diagnosis');
        Route::post('service-orders/{service_order}/complete', [ServiceOrderController::class, 'complete'])->name('api.service-orders.complete');

        Route::apiResource('service-items', ServiceItemController::class)->names('api.service-items');
        Route::patch('service-items/{service_item}/stock', [ServiceItemController::class, 'updateStock'])->name('api.service-items.stock');

        Route::post('service-orders/{service_order}/payments', [PaymentController::class, 'store'])->name('api.payments.store');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('api.payments.show');
        Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('api.payments.verify');
        Route::post('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('api.payments.reject');

        Route::get('delivery-tasks', [DeliveryTaskController::class, 'index'])->name('api.delivery-tasks.index');
        Route::get('delivery-tasks/{delivery_task}', [DeliveryTaskController::class, 'show'])->name('api.delivery-tasks.show');
        Route::post('delivery-tasks/{delivery_task}/assign', [DeliveryTaskController::class, 'assign'])->name('api.delivery-tasks.assign');
        Route::post('delivery-tasks/{delivery_task}/start', [DeliveryTaskController::class, 'start'])->name('api.delivery-tasks.start');
        Route::post('delivery-tasks/{delivery_task}/complete', [DeliveryTaskController::class, 'complete'])->name('api.delivery-tasks.complete');

        Route::get('delivery-settings', [DeliverySettingController::class, 'show'])->name('api.delivery-settings.show');
        Route::put('delivery-settings', [DeliverySettingController::class, 'update'])->name('api.delivery-settings.update');

        Route::get('invoices', [InvoiceController::class, 'index'])->name('api.invoices.index');
        Route::get('invoices/{service_order}', [InvoiceController::class, 'show'])->name('api.invoices.show');

        Route::get('notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
        Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('api.notifications.unread');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('api.dashboard');
    });
});
