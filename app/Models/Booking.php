<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model {

    protected $fillable = [
        'field_id',
        'user_id',
        'booking_start',
        'booking_end'
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}