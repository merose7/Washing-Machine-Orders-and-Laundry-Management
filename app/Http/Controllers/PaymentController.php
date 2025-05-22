<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createSnapToken(Request $request)
    {
        try {
            // Set Midtrans configuration
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$is3ds = config('midtrans.is_3ds');
            Config::$isSanitized = config('midtrans.is_sanitized');

            $bookingId = $request->input('booking_id');
            $booking = \App\Models\Booking::with('machine')->find($bookingId);

            if (!$booking) {
                return response()->json(['error' => 'Booking not found'], 404);
            }

            $grossAmount = $booking->machine && $booking->machine->price > 0 ? $booking->machine->price : 10000;

            Log::info('Midtrans grossAmount: ' . $grossAmount);

            // Harga Machine 10000 
            if ($grossAmount < 10000) {
                $grossAmount = 10000;
            }

            $params = [
                'transaction_details' => [
                    'order_id' => 'BOOKING-' . $booking->id,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $booking->customer_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate snap token'], 500);
        }
    }
}
