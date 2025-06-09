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
    public function checkPaymentStatus($id)
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        /** @var \App\Models\Booking|null $booking */
        $booking = \App\Models\Booking::find($id);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Use consistent order_id format without unique suffix
        $orderId = 'BOOKING-' . $booking->id;

        try {
            /** @var object $status */
            $status = \Midtrans\Transaction::status($orderId);

            $transactionStatus = $status->transaction_status;
            $paymentType = $status->payment_type;
            $fraudStatus = $status->fraud_status ?? null;

            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'challenge') {
                        $booking->payment_status = 'challenge';
                    } else {
                        $booking->payment_status = 'paid';
                    }
                }
            } elseif ($transactionStatus == 'settlement') {
                $booking->payment_status = 'paid';
            } elseif ($transactionStatus == 'pending') {
                $booking->payment_status = 'pending';
            } elseif ($transactionStatus == 'deny') {
                $booking->payment_status = 'deny';
            } elseif ($transactionStatus == 'expire') {
                $booking->payment_status = 'expire';
            } elseif ($transactionStatus == 'cancel') {
                $booking->payment_status = 'cancel';
            }

            // Create or update Payment record when payment is successful
            if (in_array($booking->payment_status, ['paid'])) {
                $amount = $booking->machine ? $booking->machine->price : 10000;
                $payment = \App\Models\Payment::updateOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'amount' => $amount,
                        'status' => $booking->payment_status,
                        'payment_method' => $booking->payment_method,
                    ]
                );
            }

            $booking->save();

            return response()->json([
                'status' => 'success',
                'payment_status' => $booking->payment_status,
                'transaction_status' => $transactionStatus,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Midtrans manual status check error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get transaction status'], 500);
        }
    }
    public function showBookingForm()
    {
        return app(HomeController::class)->index();
    }

    public function indexAdmin()
    {
        $bookings = \App\Models\Booking::with('machine')->orderBy('booking_time', 'desc')->paginate(10);
        return view('admin.bookings', compact('bookings'));
    }



    public function create(Request $request)
    {
        $machineId = $request->query('machine_id');
        // You can pass the machine ID to the view if needed
        return view('booking.create', compact('machineId'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'machine_id' => 'required|exists:machines,id',
                'booking_date' => 'required|date',
                'booking_time' => 'required',
                'payment_method' => 'required|in:cash,midtrans',
            ]);

            // Combine booking_date and booking_time into a single datetime string
            $bookingStart = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->booking_time . ':00');
            $bookingEnd = $bookingStart->copy()->addMinutes(60); // Default 60 minutes duration

            Log::info('Booking start: ' . $bookingStart);
            Log::info('Booking end: ' . $bookingEnd);

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

            Log::info('Booking created with ID: ' . $booking->id);

            // Update machine status to booked and set booking_ends_at
            $machine = \App\Models\Machine::find($request->machine_id);
            if ($machine) {
                $machine->status = 'booked';
                $machine->booking_ends_at = $bookingEnd; // set booking end time based on default duration
                $machine->save();
            }

            // Send notification after booking
            $this->sendNotification($booking, 'Booking berhasil dibuat. Mesin Anda dijadwalkan pada ' . $booking->booking_time);

            // Kalau pakai Midtrans, redirect ke halaman pembayaran
            if ($booking->payment_method === 'midtrans') {
                return redirect()->route('booking.payment', $booking->id);
            }

            return redirect()->route('booking.receipt', $booking->id);
        } catch (\Exception $e) {
            \Log::error('Booking store error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memproses booking. Silakan coba lagi.']);
        }
    }

public function payment($id)
{
    $booking = Booking::with('machine')->findOrFail($id);
    $machinePrice = $booking->machine ? $booking->machine->price : null;
    return view('booking.midtrans', compact('booking', 'machinePrice'));
}

    // Remove duplicate paymentNotification method

    public function receipt($id)
    {
        $booking = Booking::with('machine')->findOrFail($id);
        $machinePrice = $booking->machine ? $booking->machine->price : null;

        // Generate Midtrans snap token
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        $grossAmount = $machinePrice ?? 10000;
        $orderId = 'BOOKING-' . $booking->id;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name,
                'email' => auth()->user()->email,
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
        } catch (\Exception $e) {
            \Log::error('Midtrans Snap Token Error in receipt: ' . $e->getMessage());
            $snapToken = null;
        }

        return view('booking.receipt', compact('booking', 'machinePrice', 'snapToken'));
    }

    public function getMidtransToken($id)
    {
        $booking = Booking::with('machine')->findOrFail($id);

        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $grossAmount = $booking->machine ? $booking->machine->price : 10000;

        // Use consistent order_id format without unique suffix
        $orderId = 'BOOKING-' . $booking->id;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name,
                'email' => auth()->user()->email,
            ],
        ];

            Log::info('Midtrans grossAmount: ' . $grossAmount);
            Log::info('Midtrans orderId: ' . $orderId);
            Log::info('Midtrans customer email: ' . auth()->user()->email);

            try {
                $snapToken = Snap::getSnapToken($params);

                // Create or update Payment record with status 'pending'
                \App\Models\Payment::updateOrCreate(
                    ['booking_id' => $booking->id, 'payment_method' => 'midtrans'],
                    ['amount' => $grossAmount, 'status' => 'pending']
                );

                Log::info('Midtrans snapToken generated: ' . $snapToken);
                return response()->json(['token' => $snapToken, 'order_id' => $orderId]);
            } catch (\Exception $e) {
                Log::error('Midtrans Snap Token Error: ' . $e->getMessage());
                Log::error('Midtrans Snap Token Trace: ' . $e->getTraceAsString());
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

            // Send email notification only if user email is valid
            $user = Auth::user();
            if ($user && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($user->email)->send(new BookingConfirmation([
                    'name' => $booking->customer_name,
                    'message' => $message,
                    'booking_time' => $booking->booking_time,
                    'machine_id' => $booking->machine_id,
                ]));
            } else {
                Log::warning('Invalid or missing user email for booking notification. Booking ID: ' . $booking->id);
            }

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
            if (request()->ajax()) {
                return response()->json(['error' => 'Pembayaran bukan metode cash.'], 400);
            }
            return redirect()->back()->with('error', 'Pembayaran bukan metode cash.');
        }

        if ($booking->payment_status === 'paid') {
            if (request()->ajax()) {
                return response()->json(['info' => 'Pembayaran cash sudah dikonfirmasi.']);
            }
            return redirect()->back()->with('info', 'Pembayaran cash sudah dikonfirmasi.');
        }

        $booking->payment_status = 'paid';
        $booking->status = 'confirmed';
        $booking->save();

        // Update machine status to available when payment is confirmed
        $machine = $booking->machine;
        if ($machine) {
            $machine->status = 'available';
            $machine->booking_ends_at = null;
            $machine->save();
        }

        // Create or update Payment record for cash payment
        $amount = $booking->machine ? $booking->machine->price : 10000;
        $payment = \App\Models\Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'amount' => $amount,
                'status' => 'paid',
                'payment_method' => 'cash',
            ]
        );

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

        if (request()->ajax()) {
            // Return updated totals
            $totalCashPayments = \App\Models\Payment::where('payment_method', 'cash')
                ->where('status', 'paid')
                ->sum('amount');
            $totalMidtransPayments = \App\Models\Payment::where('payment_method', 'midtrans')
                ->whereIn('status', ['paid', 'pending', 'completed'])
                ->sum('amount');
            $totalPayments = $totalCashPayments + $totalMidtransPayments;

            return response()->json([
                'success' => 'Pembayaran cash telah dikonfirmasi.',
                'totalCashPayments' => $totalCashPayments,
                'totalMidtransPayments' => $totalMidtransPayments,
                'totalPayments' => $totalPayments,
            ]);
        }

        return redirect()->back()->with('success', 'Pembayaran cash telah dikonfirmasi.');
    }

    public function confirmReceipt($id)
    {
        $booking = Booking::findOrFail($id);

        // Update machine status to available when receipt is confirmed
        $machine = $booking->machine;
        if ($machine) {
            $machine->status = 'available';
            $machine->booking_ends_at = null;
            $machine->save();
        }

        // Optionally update booking status to completed
        $booking->status = 'completed';
        $booking->save();

        return redirect()->back()->with('success', 'Terima kasih telah menerima struk. Status mesin telah diperbarui menjadi tersedia.');
    }
}
