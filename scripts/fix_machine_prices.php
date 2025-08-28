<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

$updated = DB::table('machines')
    ->whereNull('price')
    ->orWhere('price', 0)
    ->update(['price' => 10000]);

echo "Updated $updated machines with price 0 or null to 10000.\n";
