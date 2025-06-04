<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Modify the 'status' enum to include 'pending'
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('paid', 'unpaid', 'pending') NOT NULL DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the 'status' enum to original values
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('paid', 'unpaid') NOT NULL DEFAULT 'unpaid'");
    }
};
