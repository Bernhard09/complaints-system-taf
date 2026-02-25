<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaSetting extends Model
{
    protected $fillable = [
        'first_response_hours',
        'resolution_hours',
    ];
}
