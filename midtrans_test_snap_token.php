<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Midtrans\Config;
use Midtrans\Snap;

// Load .env variables explicitly
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

Config::$serverKey = $_ENV['MIDTRANS_SERVER_KEY'] ?? null;
Config::$isProduction = ($_ENV['MIDTRANS_IS_PRODUCTION'] ?? 'false') === 'true';
Config::$is3ds = true;
Config::$isSanitized = true;

try {
    $params = [
        'transaction_details' => [
            'order_id' => 'TEST-ORDER-' . time(),
            'gross_amount' => 10000,
        ],
        'customer_details' => [
            'first_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '08123456789',
        ],
    ];

    $snapToken = Snap::getSnapToken($params);
    echo "Snap token generated successfully: " . $snapToken . PHP_EOL;
} catch (Exception $e) {
    echo "Failed to generate snap token: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
