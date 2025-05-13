<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Config;

class PaymentController extends Controller
{
    public function createSnapToken(Request $request)
    {
        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => 20000,
            ],
            'customer_details' => [
                'first_name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json(['snap_token' => $snapToken]);
    }
}
