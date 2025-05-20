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

    public function store(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'booking_duration' => 'required|integer|min:1', // duration in minutes
            'payment_method' => 'required|in:cash,midtrans',
        ]);

        // Combine booking_date and booking_time into a single datetime string
        $bookingStart = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->booking_time . ':00');
        $bookingEnd = $bookingStart->copy()->addMinutes($request->booking_duration);

        $booking = Booking::create([
            'customer_name' => Auth::user()->name,
            'machine_id' => $request->machine_id,
            'booking_time' => $bookingStart,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
        $booking->payment_status = $request->payment_method === 'cash' ? 'pending' : 'pending';
        $booking->save();

        // Update machine status to booked and set booking_ends_at
        $machine = \App\Models\Machine::find($request->machine_id);
        if ($machine) {
            $machine->status = 'booked';
            $machine->booking_ends_at = $bookingEnd; // set booking end time based on duration
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
    $booking = Booking::with('machine')->findOrFail($id);
    $machinePrice = $booking->machine ? $booking->machine->price : null;
    return view('booking.midtrans', compact('booking', 'machinePrice'));
}

    public function paymentNotification(Request $request)
    {
        // Tangani notifikasi Midtrans di sini
        $notification = $request->all();

        // Example: update booking status based on Midtrans notification
        $orderId = $notification['order_id'] ?? null;
        $transactionStatus = $notification['transaction_status'] ?? null;

        if ($orderId && $transactionStatus) {
            // Remove 'BOOKING-' prefix to get booking ID
            $bookingId = str_replace('BOOKING-', '', $orderId);
            $booking = Booking::find($bookingId);
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

        // Optionally, redirect to receipt page after payment notification
        // But since this is a webhook, just return JSON response
        return response()->json(['status' => 'success']);
    }

    public function receipt($id)
    {
        $booking = Booking::with('machine')->findOrFail($id);
        $machinePrice = $booking->machine ? $booking->machine->price : null;
        return view('booking.receipt', compact('booking', 'machinePrice'));
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
                'order_id' => 'BOOKING-' . $booking->id,
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
            // Determine payment method for notification title
            $title = 'Booking Notification';
            $paymentMethod = $booking->payment_method;

            if ($paymentMethod === 'midtrans') {
                $title = 'Gmail Notification';
            } elseif ($paymentMethod === 'cash') {
                $title = 'Cash Payment Notification';
            }

            // Save notification to DB
            Notification::create([
                'title' => $title,
                'message' => $message . ' (Booking ID ' . $booking->id . ')',
                'is_read' => false,
                'payment_method' => $paymentMethod,
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

    public function confirmCashPayment($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->payment_method !== 'cash') {
            return redirect()->back()->with('error', 'Pembayaran bukan metode cash.');
        }

        if ($booking->payment_status === 'paid') {
            return redirect()->back()->with('info', 'Pembayaran cash sudah dikonfirmasi.');
        }

        $booking->payment_status = 'paid';
        $booking->status = 'confirmed';
        $booking->save();

        // Remove existing cash payment notification for this booking
        Notification::where('payment_method', 'cash')
            ->where('message', 'like', '%Booking ID ' . $booking->id . '%')
            ->delete();

        // Create a new notification with descriptive message
        Notification::create([
            'title' => 'Konfirmasi Pembayaran Cash',
            'message' => 'Konfirmasi pembayaran cash customer untuk Booking ID ' . $booking->id . ' telah diterima.',
            'payment_method' => 'cash',
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'Pembayaran cash telah dikonfirmasi.');
    }
}
