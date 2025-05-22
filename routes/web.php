<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\MachineController;
use Illuminate\Support\Facades\Route;
use Midtrans\Snap;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\NotificationController;

// Halaman utama pelanggan
//Route::get('/laundryhome', [HomeController::class, 'index']);

// Halaman booking
Route::get('/booking', [BookingController::class, 'showBookingForm']);
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');

// New booking routes
// Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/receipt/{id}', [BookingController::class, 'receipt'])->name('booking.receipt');
Route::get('/booking/payment/{id}', [BookingController::class, 'payment'])->name('booking.payment');
Route::post('/booking/payment/notification', [BookingController::class, 'paymentNotification'])->name('booking.paymentNotification');

// Admin route
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('/admin/machines', MachineController::class);
});

// Customer route
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/laundryhome', [HomeController::class, 'index'])->name('customer.dashboard');
    Route::get('/booking', [BookingController::class, 'showBookingForm']);
});

// Debug route to check user role
Route::middleware(['auth'])->get('/debug-role', function () {
    return auth()->user() ? auth()->user()->role : 'guest';
});
//
Route::middleware('auth')->group(function () {
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/receipt/{id}', [BookingController::class, 'showReceipt'])->name('receipt.show');
});

// Grup route umum (user login)
Route::middleware(['auth'])->group(function () {
    // Profile user
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
}); 

//Notification route gmail
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
});

//Notification route cash payment
Route::middleware(['auth'])->group(function () {
    Route::post('/booking/confirm-cash/{id}', [\App\Http\Controllers\BookingController::class, 'confirmCashPayment'])->name('booking.confirmCashPayment');
});

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::post('/booking/confirm-cash/{id}', [\App\Http\Controllers\BookingController::class, 'confirmCashPayment'])->name('admin.booking.confirmCashPayment');
    Route::get('/notifications/cash', [\App\Http\Controllers\Admin\NotificationController::class, 'cashNotifications'])->name('admin.notifications.cash');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('admin.notifications.destroy');
});

//midtrans payment token
Route::post('/payment/token', [PaymentController::class, 'createSnapToken']);
Route::get('/receipt/{id}', [BookingController::class, 'showReceipt'])->name('receipt.show');

Route::middleware(['auth'])->group(function () {
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/midtrans-payment/{id}', [BookingController::class, 'midtransPayment'])->name('booking.midtrans');
    Route::get('/receipt/{id}', [BookingController::class, 'receipt'])->name('booking.receipt');
});

Route::get('/api/get-snap-token', function () {
    \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);

    $params = [
        'transaction_details' => [
            'order_id' => 'ORDER-' . time(),
            'gross_amount' => 10000,
        ]
    ];

    $snapToken = Snap::getSnapToken($params);

    return response()->json(['snap_token' => $snapToken]);
});

require __DIR__.'/auth.php';

