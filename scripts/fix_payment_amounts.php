<?php

use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Machine;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

$payments = Payment::with('booking.machine')->get();

$updatedCount = 0;

foreach ($payments as $payment) {
    $booking = $payment->booking;
    if ($booking) {
        $machine = $booking->machine;
        $price = ($machine && $machine->price > 0) ? $machine->price : 10000;
        if ($payment->amount !== $price) {
            $payment->amount = $price;
            $payment->save();
            $updatedCount++;
        }
    }
}

echo "Updated $updatedCount payment records with correct amounts based on machine prices or default 10000.\n";
