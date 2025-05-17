<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set Midtrans configuration
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Share notifications and total booking count with admin layout
        \Illuminate\Support\Facades\View::composer('layouts.admin', function ($view) {
            // Check if the notifications table has the payment_method column
            if (Schema::hasColumn('notifications', 'payment_method')) {
                $notifications = \App\Models\Notification::where('payment_method', 'cash')
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
            } else {
                // Fallback: get latest 10 notifications without filtering
                $notifications = \App\Models\Notification::orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
            }

            $totalBookingCount = \App\Models\Booking::where('payment_method', 'midtrans')
                ->whereIn('payment_status', ['paid', 'confirmed'])
                ->count();

            $view->with('notifications', $notifications)
                 ->with('totalBookingCount', $totalBookingCount);
        });
    }
}
