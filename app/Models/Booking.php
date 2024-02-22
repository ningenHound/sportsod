<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model {

    protected $fillable = [
        'field_id',
        'user_id',
        'booking_start',
        'booking_end'
    ];
}