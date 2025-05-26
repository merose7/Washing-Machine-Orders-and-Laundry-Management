<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

$newPrice = 10000;

DB::table('machines')->update(['price' => $newPrice]);

echo "All machine prices updated to Rp {$newPrice}.\n";
