<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini digunakan untuk menghubungkan aplikasi kamu dengan
    | layanan pembayaran Midtrans. Pastikan kamu telah mengisi kredensial
    | di file .env
    |
    */

    'merchant_id' => env('MIDTRANS_MERCHANT_ID', 'YOUR_MERCHANT_ID'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'YOUR_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY', 'YOUR_SERVER_KEY'),

    // Production mode: true = live, false = sandbox
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Optional: Enable 3D Secure
    'is_3ds' => true,

    // Optional: Enable data sanitization
    'is_sanitized' => true,
];
