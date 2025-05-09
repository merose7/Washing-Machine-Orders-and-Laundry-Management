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

        return view('admin.dashboard', compact(
            'totalMachines', 'totalBookings', 'totalPayments', 'totalNotifications'
        ));
    }
}
