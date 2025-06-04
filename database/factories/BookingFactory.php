<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Machine;

class BookingFactory extends Factory
{
    protected $model = \App\Models\Booking::class;

    public function definition()
    {
        return [
            'customer_name' => $this->faker->name(),
            'machine_id' => Machine::factory(),
            'booking_time' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => 'pending',
        ];
    }
}
