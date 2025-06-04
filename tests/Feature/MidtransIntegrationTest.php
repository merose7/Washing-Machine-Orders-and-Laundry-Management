<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Booking;
use App\Models\Payment;

class MidtransIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_snap_token_for_valid_booking()
    {
        $user = \App\Models\User::factory()->create();
        $booking = Booking::factory()->create();

        $response = $this->actingAs($user)->postJson('/payment/token', [
            'booking_id' => $booking->id,
            'email' => 'customer@example.com',
            'phone' => '08123456789',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['snap_token']);
    }

    /** @test */
    public function it_rejects_snap_token_generation_for_invalid_booking()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->postJson('/payment/token', [
            'booking_id' => 999999,
            'email' => 'customer@example.com',
            'phone' => '08123456789',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Booking not found']);
    }

    /** @test */
    public function it_handles_midtrans_webhook_and_updates_payment_status()
    {
        $booking = Booking::factory()->create();
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'payment_method' => 'midtrans',
            'status' => 'pending',
        ]);

        $orderId = 'BOOKING-' . $booking->id;
        $statusCode = '200';
        $grossAmount = $payment->amount;
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

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
    }
}
