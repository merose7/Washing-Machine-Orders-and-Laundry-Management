<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\MachineController;
use Illuminate\Support\Facades\Route;

// Halaman utama pelanggan
//Route::get('/laundryhome', [HomeController::class, 'index']);

// Halaman booking
Route::get('/booking', [BookingController::class, 'showBookingForm']);
Route::post('/booking', [BookingController::class, 'processBooking']);
Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');

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

// Grup route umum (user login)
Route::middleware(['auth'])->group(function () {
    // Profile user
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

