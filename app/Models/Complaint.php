<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Complaint extends Model
{
    protected $fillable = [
        'user_id',
        'contract_number',
        'complaint_reason',
        'description',
        'department_id',
        'agent_id',
        'status',
        'assigned_by',
        'resolved_at',
        // SLA
        'assigned_at',
        'first_response_at',
        'sla_response_deadline',
        'sla_resolution_deadline',

        // Escalation
        'escalation_level',
        'escalated_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'first_response_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'sla_response_deadline' => 'datetime',
        'sla_resolution_deadline' => 'datetime',
        'escalated_at' => 'datetime',
    ];


    /*
        RELATION
    */

    // department relation
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // messages relation
    public function messages()
    {
        return $this->hasMany(ComplaintMessage::class);
    }

    // internal notes relation
    public function internalNotes()
    {
        return $this->hasMany(ComplaintInternalNote::class);
    }

    // assignments history
    public function assignments()
    {
        return $this->hasMany(ComplaintAssignment::class);
    }

    // attachments relation
    public function attachments()
    {
        return $this->hasMany(ComplaintAttachment::class);
    }


    // user relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // agent relation
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    // supervisor relation
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function canBeReassigned(): bool
    {
        return in_array($this->status, ['ASSIGNED', 'IN_PROGRESS'])
            && !$this->pendingReassignment();
    }

    public function pendingReassignment()
    {
        return $this->assignments()
            ->where('status', 'PENDING')
            ->latest()
            ->first();
    }

    public function getSlaStatusAttribute()
    {
        if (!$this->sla_resolution_deadline) {
            return null;
        }

        if (now()->greaterThan($this->sla_resolution_deadline)) {
            return 'BREACHED';
        }

        if (now()->diffInHours($this->sla_resolution_deadline) <= 4) {
            return 'CRITICAL';
        }

        if (now()->diffInHours($this->sla_resolution_deadline) <= 12) {
            return 'WARNING';
        }

        return 'SAFE';
    }


    // SLA Breach Checkers
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


    public function evaluateEscalation(): ?string
    {
        // Response SLA breach → L1
        $now = now();

        /**
         * ESCALATION LEVEL 1
         * Response SLA breached
         * - agent belum merespons
         * - deadline response lewat
         */
        if (
            is_null($this->first_response_at)
            && $this->sla_response_deadline
            && $now->greaterThan($this->sla_response_deadline)
            && $this->escalation_level !== 'ESCALATION_L1'
            && $this->escalation_level !== 'ESCALATION_L2'
        ) {
            $this->update([
                'escalation_level' => 'ESCALATION_L1',
                'escalated_at' => $now,
            ]);

            return 'ESCALATION_L1';
        }

        /**
         * ESCALATION LEVEL 2
         * Resolution SLA breached
         * - complaint belum CLOSED / RESOLVED
         * - deadline resolution lewat
         */
        if (
            $this->status !== 'RESOLVED'
            && $this->sla_resolution_deadline
            && $now->greaterThan($this->sla_resolution_deadline)
        ) {
            $this->update([
                'escalation_level' => 'ESCALATION_L2',
                'escalated_at' => $now,
            ]);
            return 'ESCALATION_L2';
        }

        // =========================
        // ESCALATION LEVEL 3
        // Still unresolved
        // =========================

        if (
            $this->escalation_level === 'ESCALATION_L2'
            && $this->sla_resolution_deadline
            && $now->greaterThan($this->sla_resolution_deadline->addHours(24))
        ) {
            $this->update([
                'escalation_level' => 'ESCALATION_L3',
                'escalated_at'     => $now,
            ]);

            return 'ESCALATION_L3';
        }

        return null;
    }

}
