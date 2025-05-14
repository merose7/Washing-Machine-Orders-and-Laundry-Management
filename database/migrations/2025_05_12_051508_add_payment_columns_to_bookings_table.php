<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->enum('payment_method', ['cash', 'midtrans'])->after('status');
        $table->enum('payment_status', ['Success','pending', 'paid'])->default('pending')->after('payment_method');
    });
}

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->dropColumn('payment_method');
        $table->dropColumn('payment_status');
    });
}

};
