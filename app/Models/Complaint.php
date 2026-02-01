<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable =  [
        'user_id',
        'contract_number',
        'complaint_reason',
        'description',
        'status',
    ];

    // relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
