<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model {

    protected $fillable = [
        'booking_type',
        'booking_id',
        'booking_from',
        'booking_to',
        'user_id'
    ];
}