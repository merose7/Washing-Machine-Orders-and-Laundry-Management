<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Notification;

class CreateCashPaymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:create-cash-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create notifications for bookings with cash payment that do not have notifications yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookings = Booking::where('payment_method', 'cash')->get();

        $createdCount = 0;

        foreach ($bookings as $booking) {
            $exists = Notification::where('message', 'like', '%'.$booking->id.'%')
                ->where('payment_method', 'cash')
                ->exists();

            if (!$exists) {
                Notification::create([
                    'title' => 'Konfirmasi Pembayaran Cash',
                    'message' => 'Booking ID '.$booking->id.' dengan pembayaran cash perlu konfirmasi manual oleh admin.',
                    'is_read' => false,
                    'payment_method' => 'cash',
                ]);
                $createdCount++;
            }
        }

        $this->info("Created {$createdCount} cash payment notifications.");
    }
}
