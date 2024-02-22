<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model {

    protected $fillable = [
        'field_type',
        'venue_id'
    ];
}