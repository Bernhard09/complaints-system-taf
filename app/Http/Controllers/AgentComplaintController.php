<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class AgentComplaintController extends Controller
{
    // public function index(Request $request)
    // {
    //     $complaints = Complaint::where('agent_id', $request->user()->id)
    //         ->whereIn('status', ['IN_PROGRESS', 'WAITING_USER', 'WAITING_CONFIRMATION'])
    //         ->latest()
    //         ->get();

    //     return view('agent.complaints.index', compact('complaints'));
    // }
    public function index(Request $request)
    {

        $agent = $request->user();

            //     dd(Complaint::forAgent($agent->id)
            // ->select('id','sla_resolution_deadline')
            // ->get());

        $statuses = [
            'ASSIGNED',
            'IN_PROGRESS',
            'WAITING_USER',
            'WAITING_CONFIRMATION',
            'RESOLVED',
        ];

        $perPage = $request->per_page ?? 50;

        /*
        |--------------------------------------------------------------------------
        | Base Query (SATU SUMBER DATA)
        |--------------------------------------------------------------------------
        */

        $baseQuery = Complaint::forAgent($agent->id)
            ->with('user');

        /*
        |--------------------------------------------------------------------------
        | DATA UNTUK KANBAN (SEMUA TANPA FILTER STATUS TAB)
        |--------------------------------------------------------------------------
        */

        $complaints = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->get();

        $board = $complaints->groupBy('status');

        foreach ($statuses as $status) {
            if (!isset($board[$status])) {
                $board[$status] = collect();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TABLE MODE (FILTER BERDASARKAN STATUS TAB)
        |--------------------------------------------------------------------------
        */

        $tableQuery = clone $baseQuery;

        if ($request->status && $request->status !== 'ALL') {
            $tableQuery->where('status', $request->status);
        }

        $allTickets = $tableQuery
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | SLA & METRICS
        |--------------------------------------------------------------------------
        */

        $breached = $complaints->filter(fn($c) => $c->sla_status === 'BREACHED');
        $critical = $complaints->filter(fn($c) => $c->sla_status === 'CRITICAL');

        $resolvedToday = Complaint::forAgent($agent->id)
            ->whereDate('confirmed_at', now())
            ->count();

        $activeCount = $complaints->whereIn('status', [
            'ASSIGNED',
            'IN_PROGRESS'
        ])->count();

        $waitingUser = $complaints->where('status', 'WAITING_USER')->count();

        $breachCount = $breached->count();

        $tableQuery = clone $baseQuery;

        // Status Filter
        if ($request->status && $request->status !== 'ALL') {
            $tableQuery->where('status', $request->status);
        }

        // Date From
        if ($request->from) {
            $tableQuery->whereDate('created_at', '>=', $request->from);
        }

        // Date To
        if ($request->to) {
            $tableQuery->whereDate('created_at', '<=', $request->to);
        }

        // Search Filter
        if ($request->search) {
            $search = $request->search;
            $tableQuery->where(function ($q) use ($search) {
                $q->where('contract_number', 'ilike', "%{$search}%")
                  ->orWhere('complaint_reason', 'ilike', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'ilike', "%{$search}%");
                  });
            });
        }
        return view('agent.dashboard', [
            'pageTitle' => 'Dashboard',
            'board' => $board,
            'breached' => $breached,
            'critical' => $critical,
            'allTickets' => $allTickets,
            'metrics' => [
                'active' => $activeCount,
                'waiting' => $waitingUser,
                'breached' => $breachCount,
                'resolved_today' => $resolvedToday,
            ]
        ]);
    }

    public function assigned()
    {
        return view('agent.assigned', [
            'pageTitle' => 'My Assigned'
        ]);
    }

    public function history(Request $request)
    {
        $agent = $request->user();

        $query = Complaint::where('agent_id', $agent->id)
            ->whereIn('status', ['RESOLVED', 'CLOSED'])
            ->with('user');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('contract_number', 'like', "%{$request->search}%")
                ->orWhere('complaint_reason', 'like', "%{$request->search}%");
            });
        }

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $tickets = $query->latest()->paginate(20)->withQueryString();

        return view('agent.history', compact('tickets'));
    }

    public function sla(Request $request)
    {
        $agent = $request->user();

        // Response SLA: my tickets with no first response yet, at risk or breached
        $responseTickets = Complaint::where('agent_id', $agent->id)
            ->with('user')
            ->whereNotNull('sla_response_deadline')
            ->whereNull('first_response_at')
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where(function ($q) {
                $q->where('sla_response_deadline', '<', now())
                  ->orWhere('sla_response_deadline', '<=', now()->addHours(12));
            })
            ->orderBy('sla_response_deadline')
            ->get();

        // Resolution SLA: my tickets with deadline approaching or breached
        $resolutionTickets = Complaint::where('agent_id', $agent->id)
            ->with('user')
            ->whereNotNull('sla_resolution_deadline')
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where(function ($q) {
                $q->where('sla_resolution_deadline', '<', now())
                  ->orWhere('sla_resolution_deadline', '<=', now()->addHours(12));
            })
            ->orderBy('sla_resolution_deadline')
            ->get();

        $metrics = [
            'response_breached' => Complaint::where('agent_id', $agent->id)
                ->whereNotNull('sla_response_deadline')
                ->whereNull('first_response_at')
                ->where('sla_response_deadline', '<', now())
                ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
                ->count(),
            'resolution_breached' => Complaint::where('agent_id', $agent->id)
                ->whereNotNull('sla_resolution_deadline')
                ->where('sla_resolution_deadline', '<', now())
                ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
                ->count(),
            'critical' => $resolutionTickets->filter(fn($c) => $c->sla_status === 'CRITICAL')->count(),
            'warning' => $resolutionTickets->filter(fn($c) => $c->sla_status === 'WARNING')->count(),
        ];

        return view('agent.sla', compact('responseTickets', 'resolutionTickets', 'metrics'));
    }

    public function markWaiting(Request $request,Complaint $complaint)
    {

        $user = $request->user();

        abort_unless(
            $user->role === 'AGENT'
            && $complaint->agent_id === $user->id,
            403
        );

        $complaint->update([
            'status' => 'WAITING_USER',
        ]);

        // Notify user
        NotificationService::send(
            $complaint->user_id, 'warning',
            'Response Needed',
            "Agent is waiting for your response on complaint #{$complaint->id}.",
            route('complaints.show', $complaint)
        );

        return back()->with('success', 'Waiting for user response');
    }

    public function requestConfirmation(Request $request, Complaint $complaint)
    {
        $user = $request->user();

        abort_unless(
            $user->role === 'AGENT'
            && $complaint->agent_id === $user->id
            && $complaint->status === 'IN_PROGRESS',
            403
        );

        $complaint->update([
            'status' => 'WAITING_CONFIRMATION',
            'confirmation_requested_at' => now(),
        ]);

        // Notify user
        NotificationService::send(
            $complaint->user_id, 'info',
            'Resolution Confirmation Requested',
            "Agent requested confirmation that complaint #{$complaint->id} is resolved.",
            route('complaints.show', $complaint)
        );

        return back()->with('success', 'Waiting for user confirmation');
    }

    public function close(Request $request, Complaint $complaint)
    {
        $user = $request->user();

        abort_unless(
            $user->role === 'AGENT'
            && $complaint->agent_id === $user->id
            && $complaint->status === 'WAITING_CONFIRMATION',
            403
        );

        $complaint->update([
            'status' => 'CLOSED',
        ]);

        return back()->with('success', 'Complaint closed');
    }

    public function confirmReassign(Request $request, \App\Models\ComplaintAssignment $assignment)
    {
        $user = $request->user();

        // Only the current (old) agent can confirm
        abort_unless(
            $user->role === 'AGENT'
            && $assignment->from_agent_id === $user->id
            && $assignment->status === 'PENDING',
            403
        );

        $complaint = $assignment->complaint;
        $assignedAt = now();

        // Update assignment record
        $assignment->update([
            'status' => 'CONFIRMED',
            'confirmed_at' => $assignedAt,
        ]);

        // Transfer complaint to new agent
        $complaint->update([
            'department_id' => $assignment->to_department_id,
            'agent_id' => $assignment->to_agent_id,
            'assigned_by' => $assignment->assigned_by,
            'assigned_at' => $assignedAt,
            'status' => 'ASSIGNED',
            'first_response_at' => null,
            'sla_response_deadline' => $assignedAt->copy()->addHours(24),
            'sla_resolution_deadline' => $assignment->sla_resolution_deadline,
        ]);

        // Add system message in chat
        \App\Models\ComplaintMessage::create([
            'complaint_id' => $complaint->id,
            'sender_id' => $user->id,
            'sender_role' => $user->role,
            'message' => "⟳ This complaint has been reassigned from {$user->name} to a new agent. Reason: {$assignment->reason}",
            'is_system' => true,
        ]);

        // Internal note
        \App\Models\ComplaintInternalNote::create([
            'complaint_id' => $complaint->id,
            'author_id' => $user->id,
            'author_role' => $user->role,
            'note' => "Reassign confirmed by {$user->name}. Transferred to Agent ID {$assignment->to_agent_id}.",
        ]);

        // Notify new agent
        NotificationService::send(
            $assignment->to_agent_id, 'info',
            'New Complaint Assigned',
            "Complaint #{$complaint->id} has been reassigned to you.",
            route('complaints.show', $complaint)
        );

        // Notify supervisor
        if ($assignment->assigned_by) {
            NotificationService::send(
                $assignment->assigned_by, 'success',
                'Reassign Confirmed',
                "Agent {$user->name} confirmed reassignment of complaint #{$complaint->id}.",
                route('supervisor.complaints.show', $complaint)
            );
        }

        return redirect()->route('agent.dashboard')->with('success', 'Reassign confirmed. Complaint transferred.');
    }

    public function rejectReassign(Request $request, \App\Models\ComplaintAssignment $assignment)
    {
        $user = $request->user();

        abort_unless(
            $user->role === 'AGENT'
            && $assignment->from_agent_id === $user->id
            && $assignment->status === 'PENDING',
            403
        );

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5'],
        ]);

        $complaint = $assignment->complaint;

        // Update assignment record
        $assignment->update([
            'status' => 'REJECTED',
            'rejection_reason' => $request->rejection_reason,
            'confirmed_at' => now(),
        ]);

        // Revert complaint status
        $complaint->update([
            'status' => 'IN_PROGRESS',
        ]);

        // Internal note
        \App\Models\ComplaintInternalNote::create([
            'complaint_id' => $complaint->id,
            'author_id' => $user->id,
            'author_role' => $user->role,
            'note' => "Reassign rejected by {$user->name}. Reason: {$request->rejection_reason}",
        ]);

        // Notify supervisor
        if ($assignment->assigned_by) {
            NotificationService::send(
                $assignment->assigned_by, 'error',
                'Reassign Rejected',
                "Agent {$user->name} rejected reassignment of complaint #{$complaint->id}. Reason: {$request->rejection_reason}",
                route('supervisor.complaints.show', $complaint)
            );
        }

        return back()->with('success', 'Reassign rejected. Complaint remains with you.');
    }

}
