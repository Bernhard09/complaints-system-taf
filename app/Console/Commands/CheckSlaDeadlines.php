<?php

namespace App\Console\Commands;

use App\Models\Complaint;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckSlaDeadlines extends Command
{
    protected $signature = 'sla:check';
    protected $description = 'Check SLA deadlines and send notifications for approaching/breached complaints';

    public function handle()
    {
        $now = now();

        // 1. Resolution approaching deadline (within 6 hours)
        $approaching = Complaint::whereNotNull('sla_resolution_deadline')
            ->whereIn('status', ['ASSIGNED', 'IN_PROGRESS', 'WAITING_USER'])
            ->where('sla_resolution_deadline', '>', $now)
            ->where('sla_resolution_deadline', '<=', $now->copy()->addHours(6))
            ->get();

        foreach ($approaching as $complaint) {
            $hoursLeft = round($now->diffInMinutes($complaint->sla_resolution_deadline) / 60, 1);

            // Notify agent
            if ($complaint->agent_id) {
                NotificationService::send(
                    $complaint->agent_id, 'warning',
                    'SLA Approaching Deadline',
                    "Complaint #{$complaint->id} resolution deadline in {$hoursLeft}h.",
                    route('complaints.show', $complaint)
                );
            }

            // Notify supervisors
            NotificationService::sendToRole(
                'SUPERVISOR', 'warning',
                'SLA Approaching Deadline',
                "Complaint #{$complaint->id} resolution deadline in {$hoursLeft}h.",
                route('supervisor.complaints.show', $complaint)
            );
        }

        // 2. Resolution breached
        $breached = Complaint::whereNotNull('sla_resolution_deadline')
            ->whereIn('status', ['ASSIGNED', 'IN_PROGRESS', 'WAITING_USER'])
            ->where('sla_resolution_deadline', '<', $now)
            ->get();

        foreach ($breached as $complaint) {
            $hoursOver = round($now->diffInMinutes($complaint->sla_resolution_deadline) / 60, 1);

            if ($complaint->agent_id) {
                NotificationService::send(
                    $complaint->agent_id, 'error',
                    'SLA Breached',
                    "Complaint #{$complaint->id} resolution deadline exceeded by {$hoursOver}h!",
                    route('complaints.show', $complaint)
                );
            }

            NotificationService::sendToRole(
                'SUPERVISOR', 'error',
                'SLA Breached',
                "Complaint #{$complaint->id} resolution deadline exceeded by {$hoursOver}h!",
                route('supervisor.complaints.show', $complaint)
            );
        }

        // 3. Response approaching deadline (within 4 hours)
        $responseApproaching = Complaint::whereNotNull('sla_response_deadline')
            ->where('status', 'ASSIGNED')
            ->whereNull('first_response_at')
            ->where('sla_response_deadline', '>', $now)
            ->where('sla_response_deadline', '<=', $now->copy()->addHours(4))
            ->get();

        foreach ($responseApproaching as $complaint) {
            $hoursLeft = round($now->diffInMinutes($complaint->sla_response_deadline) / 60, 1);

            if ($complaint->agent_id) {
                NotificationService::send(
                    $complaint->agent_id, 'warning',
                    'Response Deadline Approaching',
                    "Complaint #{$complaint->id} needs first response within {$hoursLeft}h.",
                    route('complaints.show', $complaint)
                );
            }
        }

        $this->info("SLA check complete. Approaching: {$approaching->count()}, Breached: {$breached->count()}, Response approaching: {$responseApproaching->count()}");
    }
}
