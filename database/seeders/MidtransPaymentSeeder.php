<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Booking;

class MidtransPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the first booking
        $booking = Booking::first();

        if (!$booking) {
            $this->command->info('No bookings found. Please create a booking first.');
            return;
        }

        // Check if a midtrans payment already exists for this booking
        $existingPayment = Payment::where('booking_id', $booking->id)
            ->where('payment_method', 'midtrans')
            ->first();

        if ($existingPayment) {
            $this->command->info('Midtrans payment already exists for booking ID ' . $booking->id);
            return;
        }

        // Create a new midtrans payment
        Payment::create([
            'booking_id' => $booking->id,
            'amount' => 10000,
            'status' => 'paid',
            'payment_method' => 'midtrans',
        ]);

        $this->command->info('Midtrans payment created for booking ID ' . $booking->id);
    }
}
