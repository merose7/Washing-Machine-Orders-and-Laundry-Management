<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Notification;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $totalMachines = Machine::count();
        $totalBookings = Booking::count();
        $totalPayments = Payment::sum('amount'); // sum of payment amounts
        $totalNotifications = Notification::count(); // asumsi ada model Notification

        $totalCashPayments = Payment::where('payment_method', 'cash')
            ->where('status', 'paid')
            ->sum('amount');

        // Fetch recent bookings with payment relation eager loaded
        $recentBookings = Booking::with(['machine', 'payment'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $recentNotifications = Notification::orderBy('created_at', 'desc')->take(10)->get();

        $recentGmailNotifications = Notification::where('payment_method', '!=', 'cash')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $midtransBookings = Booking::with('machine')
            ->where('payment_method', 'midtrans')
            ->get();

        $totalMidtransPayments = Payment::where('payment_method', 'midtrans')->sum('amount');
        $totalMidtransBookings = Booking::where('payment_method', 'midtrans')->count();

        return view('admin.dashboard', compact(
            'totalMachines', 'totalBookings', 'totalPayments', 'totalNotifications',
            'totalCashPayments',
            'recentBookings', 'recentNotifications', 'recentGmailNotifications',
            'midtransBookings', 'totalMidtransPayments', 'totalMidtransBookings'
        ));
    }
}
