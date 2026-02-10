<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    public function isResponseSlaBreached(): bool
    {
        return $this->status === 'ASSIGNED'
            && $this->sla_response_deadline
            && now()->greaterThan($this->sla_response_deadline);
    }

    public function isResolutionSlaBreached(): bool
    {
        return $this->status !== 'RESOLVED'
            && $this->sla_resolution_deadline
            && now()->greaterThan($this->sla_resolution_deadline);
    }

     // Response SLA breached
    public function scopeResponseSlaBreached(Builder $query)
    {
        return $query
            ->where('status', 'ASSIGNED')
            ->whereNotNull('sla_response_deadline')
            ->where('sla_response_deadline', '<', now());
    }

    // Resolution SLA breached
    public function scopeResolutionSlaBreached(Builder $query)
    {
        return $query
            ->whereNotIn('status', ['RESOLVED'])
            ->whereNotNull('sla_resolution_deadline')
            ->where('sla_resolution_deadline', '<', now());
    }

}
