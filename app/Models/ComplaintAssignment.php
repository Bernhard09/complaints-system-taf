<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintAssignment extends Model
{
    protected $fillable = [
        'complaint_id',
        'from_agent_id',
        'to_agent_id',
        'to_department_id',
        'assigned_by',
        'reason',
        'status',
        'rejection_reason',
        'confirmed_at',
        'sla_resolution_deadline',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'sla_resolution_deadline' => 'datetime',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function fromAgent()
    {
        return $this->belongsTo(User::class, 'from_agent_id');
    }

    public function toAgent()
    {
        return $this->belongsTo(User::class, 'to_agent_id');
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'CONFIRMED';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }
}
