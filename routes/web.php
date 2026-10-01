<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliverySettingController;
use App\Http\Controllers\DeliveryTaskController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceItemController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('vehicles', VehicleController::class);

    Route::resource('bookings', BookingController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::patch('bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::patch('bookings/{booking}/arrive', [BookingController::class, 'arrive'])->name('bookings.arrive');

    Route::resource('service-orders', ServiceOrderController::class)->only(['index', 'show']);
    Route::patch('service-orders/{service_order}/start', [ServiceOrderController::class, 'start'])->name('service-orders.start');
    Route::patch('service-orders/{service_order}/diagnosis', [ServiceOrderController::class, 'diagnosis'])->name('service-orders.diagnosis');
    Route::patch('service-orders/{service_order}/complete', [ServiceOrderController::class, 'complete'])->name('service-orders.complete');
    Route::post('service-orders/{service_order}/items', [ServiceOrderController::class, 'addItem'])->name('service-orders.items.store');

    Route::resource('service-items', ServiceItemController::class);
    Route::patch('service-items/{service_item}/stock', [ServiceItemController::class, 'updateStock'])->name('service-items.stock');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('service-orders/{service_order}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::patch('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::patch('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    Route::resource('delivery-tasks', DeliveryTaskController::class)->only(['index', 'show']);
    Route::patch('delivery-tasks/{delivery_task}/assign', [DeliveryTaskController::class, 'assign'])->name('delivery-tasks.assign');
    Route::patch('delivery-tasks/{delivery_task}/start', [DeliveryTaskController::class, 'start'])->name('delivery-tasks.start');
    Route::patch('delivery-tasks/{delivery_task}/complete', [DeliveryTaskController::class, 'complete'])->name('delivery-tasks.complete');

    Route::get('delivery-settings', [DeliverySettingController::class, 'show'])->name('delivery-settings.show');
    Route::patch('delivery-settings', [DeliverySettingController::class, 'update'])->name('delivery-settings.update');

    Route::get('service-orders/{service_order}/invoice', [InvoiceController::class, 'show'])->name('invoices.show');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

// ── Admin ────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
});

// ── Mekanik ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:mechanic'])->prefix('mekanik')->name('mechanic.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'mechanic'])->name('dashboard');
});

// ── Kurir ────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:courier'])->prefix('kurir')->name('courier.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'courier'])->name('dashboard');
});

require __DIR__.'/auth.php';
