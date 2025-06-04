<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'booking_id' => 1, // This should be replaced with a valid booking id in tests
            'payment_method' => 'midtrans',
            'status' => 'unpaid',
            'amount' => $this->faker->numberBetween(10000, 50000),
        ];
    }
}
