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
        // Basic stub implementation for Midtrans callback
        // You can add your logic here to handle the callback data

        // For example, log the request data
        Log::info('Midtrans callback received', $request->all());

        // Return a 200 OK response to acknowledge receipt
        return response()->json(['message' => 'Callback received'], 200);
    }
}
