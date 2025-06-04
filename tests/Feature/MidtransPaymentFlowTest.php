<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class MidtransPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with admin role for dashboard access
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create a booking for testing
        $this->booking = Booking::factory()->create([
            'customer_name' => 'Test Customer',
            'machine_id' => 1,
            'booking_time' => now(),
        ]);
    }

    /** @test */
    public function it_generates_snap_token_successfully()
    {
        // Mock Midtrans Snap API response
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/snap/token' => Http::response(['token' => 'fake-snap-token'], 200),
        ]);

        $response = $this->postJson('/payment/token', [
            'booking_id' => $this->booking->id,
            'email' => 'test@example.com',
            'phone' => '08123456789',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['snap_token']);
    }

    /** @test */
    public function it_handles_midtrans_webhook_and_updates_payment_status()
    {
        // Create a payment with pending status
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'payment_method' => 'midtrans',
            'status' => 'pending',
            'amount' => 10000,
        ]);

        $webhookPayload = [
            'transaction_status' => 'settlement',
            'order_id' => 'BOOKING-' . $this->booking->id,
            'payment_type' => 'credit_card',
            'transaction_id' => 'trx-123',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson('/api/midtrans/callback', $webhookPayload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
    }

    /** @test */
    public function admin_dashboard_displays_midtrans_payments()
    {
        // Create a midtrans payment
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'payment_method' => 'midtrans',
            'status' => 'paid',
            'amount' => 10000,
        ]);

        $response = $this->actingAs($this->user)->get('/admin/payments');

        $response->assertStatus(200);
        $response->assertSee('Total Pembayaran Midtrans');
        $response->assertSee(number_format($payment->amount, 0, ',', '.'));
    }

    /** @test */
    public function it_handles_edge_case_invalid_webhook_payload()
    {
        $response = $this->postJson('/api/midtrans/callback', [
            'order_id' => 'invalid',
            'transaction_status' => 'unknown_status',
        ]);

        $response->assertStatus(400);
    }
}
