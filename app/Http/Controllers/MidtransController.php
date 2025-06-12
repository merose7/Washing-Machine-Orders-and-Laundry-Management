<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    /**
     * Handle Midtrans callback request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        $notification = $request->all();


        Log::info('Midtrans callback received', $notification);

        // Verify signature key
        $serverKey = config('midtrans.server_key');
        $orderId = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? null;
        $signatureKey = $notification['signature_key'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            Log::error('Midtrans callback missing required fields');
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        $input = $orderId . $statusCode . $grossAmount . $serverKey;
        $hashed = hash('sha512', $input);

        Log::info('Midtrans callback signature verification', ['calculated' => $hashed, 'received' => $signatureKey]);

        if ($hashed !== $signatureKey) {
            Log::error('Midtrans callback invalid signature key');
            return response()->json(['error' => 'Invalid signature key'], 403);
        }

        // Extract booking ID from order ID
        $bookingId = null;

        
        if (preg_match('/BOOKING-(\d+)-\d+/', $orderId, $matches)) {
            $bookingId = $matches[1];
        } elseif (preg_match('/payment_notif_test_[^_]+_(\d+)/', $orderId, $matches)) {
            $bookingId = $matches[1];
        } else {
            // Try to extract numeric ID from the order ID string as fallback
            if (preg_match('/(\d+)/', $orderId, $matches)) {
                $bookingId = $matches[1];
            }
        }

        if (!$bookingId) {
            Log::error('Midtrans callback invalid order ID format: ' . $orderId);
            return response()->json(['error' => 'Invalid order ID format'], 400);
        }

        $transactionStatus = $notification['transaction_status'] ?? null;
        $paymentType = $notification['payment_type'] ?? null;
        $fraudStatus = $notification['fraud_status'] ?? null;

        $booking = \App\Models\Booking::find($bookingId);
        if (!$booking) {
            Log::error('Midtrans callback booking not found: ' . $bookingId);
            return response()->json(['error' => 'Booking not found'], 404);
        }

        Log::info('Midtrans callback booking found', ['booking_id' => $bookingId, 'current_payment_status' => $booking->payment_status]);

        // Update booking payment_status based on transaction_status
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

        Log::info('Midtrans callback updating payment_status', ['new_payment_status' => $booking->payment_status]);

        // Create or update Payment record when payment is successful
        if (in_array($booking->payment_status, ['paid'])) {
            $amount = $booking->machine ? $booking->machine->price : 10000;
            \App\Models\Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'amount' => $amount,
                    'status' => $booking->payment_status,
                    'payment_method' => $booking->payment_method,
                ]
            );
        }

        $booking->save();

        Log::info('Midtrans callback booking saved', ['booking_id' => $bookingId, 'payment_status' => $booking->payment_status]);

        return response()->json(['message' => 'Midtrans notification processed successfully'], 200);
    }
}
