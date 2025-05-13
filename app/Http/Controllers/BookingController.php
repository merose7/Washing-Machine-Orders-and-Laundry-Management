<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Booking;
use App\Mail\BookingConfirmation;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class BookingController extends Controller
{
    public function showBookingForm()
    {
        return app(HomeController::class)->index();
    }

    public function create(Request $request)
    {
        $machineId = $request->query('machine_id');
        // You can pass the machine ID to the view if needed
        return view('booking.create', compact('machineId'));
    }

    // Removed processBooking method as store method handles booking creation and redirect
    public function store(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'booking_time' => 'required|date',
            'payment_method' => 'required|in:cash,midtrans',
        ]);

        $booking = Booking::create([
            'customer_name' => Auth::user()->name,
            'machine_id' => $request->machine_id,
            'booking_time' => $request->booking_time,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        // Update machine status to booked
        $machine = \App\Models\Machine::find($request->machine_id);
        if ($machine) {
            $machine->status = 'dibooking';
            $machine->save();
        }

        // Send notification after booking
        $this->sendNotification($booking, 'Booking berhasil dibuat. Mesin Anda dijadwalkan pada ' . $booking->booking_time);

        // Kalau pakai Midtrans, redirect ke halaman pembayaran
        if ($booking->payment_method === 'midtrans') {
            return redirect()->route('booking.payment', $booking->id);
        }

        return redirect()->route('booking.receipt', $booking->id);
    }

public function payment($id)
{
    $booking = Booking::findOrFail($id);
    // Lanjutkan logika redirect ke Snap Midtrans di sini
    return view('booking.midtrans', compact('booking'));
}

    public function paymentNotification(Request $request)
    {
        // Tangani notifikasi Midtrans di sini
        $notification = $request->all();

        // Example: update booking status based on Midtrans notification
        $orderId = $notification['order_id'] ?? null;
        $transactionStatus = $notification['transaction_status'] ?? null;

        if ($orderId && $transactionStatus) {
            $booking = Booking::find($orderId);
            if ($booking) {
                if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                    $booking->payment_status = 'paid';
                    $booking->status = 'confirmed';
                    $booking->save();

                    // Send notification to customer here (email/SMS)
                    $this->sendNotification($booking, 'Pembayaran berhasil diterima. Mesin Anda dijadwalkan pada ' . $booking->booking_time);
                } elseif ($transactionStatus === 'cancel' || $transactionStatus === 'deny' || $transactionStatus === 'expire') {
                    $booking->payment_status = 'failed';
                    $booking->status = 'cancelled';
                    $booking->save();
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function receipt($id)
    {
        $booking = Booking::findOrFail($id);
        return view('booking.receipt', compact('booking'));
    }

    public function getMidtransToken($id)
    {
        $booking = Booking::findOrFail($id);

        // Set Midtrans configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $booking->id,
                'gross_amount' => 10000, 
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name,
                'email' => auth()->user()->email,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get snap token'], 500);
        }
    }

    private function sendNotification($booking, $message)
    {
        try {
            // Save notification to DB
            Notification::create([
                'title' => 'Booking Notification',
                'message' => $message,
                'is_read' => false,
            ]);

            // Send email notification
            Mail::to(Auth::user()->email)->send(new BookingConfirmation([
                'name' => $booking->customer_name,
                'message' => $message,
                'booking_time' => $booking->booking_time,
                'machine_id' => $booking->machine_id,
            ]));

            // TODO: Implement SMS sending if needed

        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
        }
    }
    public function showReceipt($id)
{
    $booking = Booking::findOrFail($id);

    return view('booking.receipt', [
        'booking' => $booking
    ]);
}

}
