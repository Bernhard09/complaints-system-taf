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
        'department_id',
        'agent_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function messages()
    {
        return $this->hasMany(ComplaintMessage::class);
    }

    public function internalNotes()
    {
        return $this->hasMany(ComplaintInternalNote::class);
    }

    // relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
