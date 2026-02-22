<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintAssignment extends Model
{
    protected $fillable = [
        'complaint_id',
        'from_agent_id',
        'to_agent_id',
        'assigned_by',
        'reason',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}

