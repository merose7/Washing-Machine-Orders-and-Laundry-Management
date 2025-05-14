<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name', 'machine_id', 'booking_time', 'status', 'payment_method', 'payment_status'
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
