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
        $totalPayments = Payment::count(); // asumsi ada model Payment
        $totalNotifications = Notification::count(); // asumsi ada model Notification

        $recentBookings = Booking::orderBy('created_at', 'desc')->take(10)->get();
        $recentNotifications = Notification::orderBy('created_at', 'desc')->take(10)->get();

        $recentGmailNotifications = Notification::where('payment_method', '!=', 'cash')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalMachines', 'totalBookings', 'totalPayments', 'totalNotifications',
            'recentBookings', 'recentNotifications', 'recentGmailNotifications'
        ));
    }
}
