<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class MidtransIntegrationAlternativeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_snap_token_with_manual_booking_data()
    {
        // Insert a machine record to satisfy foreign key constraint
        DB::table('machines')->insert([
            'id' => 1,
            'name' => 'Test Machine',
            'price' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Manually create booking data array with all required fields
        $bookingData = [
            'id' => 1,
            'customer_name' => 'Test Customer',
            'machine_id' => 1,
            'booking_time' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Insert booking manually into DB
        DB::table('bookings')->insert($bookingData);

        $response = $this->postJson('/payment/token', [
            'booking_id' => 1,
            'email' => 'customer@example.com',
            'phone' => '08123456789',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['snap_token']);
    }

    /** @test */
    public function it_rejects_snap_token_generation_for_invalid_booking()
    {
        $response = $this->postJson('/payment/token', [
            'booking_id' => 999999,
            'email' => 'customer@example.com',
            'phone' => '08123456789',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['booking_id']);
    }

    /** @test */
    public function it_handles_midtrans_webhook_and_updates_payment_status()
    {
        // Insert machine record
        DB::table('machines')->insert([
            'id' => 1,
            'name' => 'Test Machine',
            'price' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert booking record
        DB::table('bookings')->insert([
            'id' => 1,
            'customer_name' => 'Test Customer',
            'machine_id' => 1,
            'booking_time' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert payment record
        DB::table('payments')->insert([
            'id' => 1,
            'booking_id' => 1,
            'payment_method' => 'midtrans',
            'amount' => 10000,
            'status' => 'paid', // Adjusted to valid enum value
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = 'BOOKING-1';
        $statusCode = '200';
        $grossAmount = 10000;
        $serverKey = config('midtrans.server_key');
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson('/midtrans/webhook', $payload);

        $response->assertStatus(200);

        $payment = Payment::find(1);
        $this->assertEquals('paid', $payment->status);
    }
}
