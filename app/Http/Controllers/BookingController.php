<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function showBookingForm()
    {
        return app(HomeController::class)->index();
    }

    public function processBooking(Request $request)
    {
        // Validasi dan proses booking
        $validated = $request->validate([
            'name' => 'required|string',
            'time' => 'required|date',
            'payment' => 'required|in:cash,midtrans',
        ]);

        // Kirim notifikasi email ke pelanggan
        Mail::to($request->user())->send(new \App\Mail\BookingConfirmation($validated));

        // Redirect atau tampilkan notifikasi
        return redirect()->back()->with('success', 'Booking Successful! Check your email for confirmation.');
    }
}
