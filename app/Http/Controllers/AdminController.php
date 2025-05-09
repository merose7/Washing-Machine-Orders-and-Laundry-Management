<?php

use App\Models\Machine;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Notification;

class AdminController
{
    public function dashboard()
    {
        $totalMachines = Machine::count();
        $totalBookings = Booking::count();
        $totalPayments = Payment::count();
        $totalNotifications = Notification::count();

        return view('admin.dashboard', compact(
            'totalMachines',
            'totalBookings',
            'totalPayments',
            'totalNotifications'
        ));
    }
}
