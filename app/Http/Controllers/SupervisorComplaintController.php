<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use App\Models\ComplaintAssignment;
use App\Models\ComplaintInternalNote;


use Illuminate\Http\Request;
use App\Services\NotificationService;

class SupervisorComplaintController extends Controller
{
    //
    public function index(Request $request)
    {

        $columns = [
            'SUBMITTED' => 'SUBMITTED',
            'ASSIGNED' => 'ASSIGNED',
            'IN_PROGRESS' => 'IN PROGRESS',
            'WAITING_USER' => 'WAITING USER',
            'RESOLVED' => 'RESOLVED',
        ];

        $metrics = [
            'incoming' => Complaint::where('status', 'SUBMITTED')->count(),
            'assigned' => Complaint::where('status', 'ASSIGNED')->count(),
            'in_progress' => Complaint::where('status', 'IN_PROGRESS')->count(),
            'breached' => Complaint::resolutionSlaBreached()->count(),
            'resolved_today' => Complaint::whereDate('resolved_at', now())->count(),
        ];

        $board = [];

        foreach ($columns as $status => $label) {
            $board[$status] = Complaint::with(['user', 'agent'])
                ->where('status', $status)
                ->latest()
                ->take(20)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | TABLE MODE (paginated + filtered)
        |--------------------------------------------------------------------------
        */
        $perPage = $request->per_page ?? 50;

        $tableQuery = Complaint::with(['user', 'agent']);

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

        $allTickets = $tableQuery
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('supervisor.dashboard', compact('board', 'columns', 'metrics', 'allTickets'));
    }

    public function show(Complaint $complaint)
    {
        $complaint->load(['user', 'agent', 'attachments']);
        $departments = Department::with(['agents' => function ($q) {
            $q->where('role', 'AGENT');
        }])->get();
        $agents = User::where('role', 'AGENT')->get();

        //dd($departments->toArray());

        return view('supervisor.complaints.show', compact(
            'complaint',
            'agents',
            'departments',
        ));
    }


    // Assign complaint ke department
    public function assign(Request $request, Complaint $complaint)
    {

        $supervisor = $request->user();
        $assignedAt = now();

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'agent_id' => ['required', 'exists:users,id'],
        ]);

        // pastikan agent memang AGENT dan satu department
        $agent = User::where('id', $validated['agent_id'])
            ->where('role', 'AGENT')
            ->where('department_id', $validated['department_id'])
            ->firstOrFail();
        //dd($agent);
        $complaint->update([
            'department_id' => $validated['department_id'],
            'agent_id' => $agent->id,
            'assigned_by' => $supervisor->id,
            'status'   => 'ASSIGNED',

            // SLA start
            'assigned_at' => $assignedAt,
            'sla_response_deadline' => $assignedAt->copy()->addHours(24),
            'sla_resolution_deadline' => $assignedAt->copy()->addDays(3),
        ]);

        // Notify agent
        NotificationService::send(
            $agent->id, 'info',
            'New Complaint Assigned',
            "Complaint #{$complaint->id} has been assigned to you.",
            route('complaints.show', $complaint)
        );

        // Notify user
        NotificationService::send(
            $complaint->user_id, 'info',
            'Complaint Assigned',
            "Your complaint #{$complaint->id} has been assigned to an agent.",
            route('complaints.show', $complaint)
        );

        return back()->with('success', 'Complaint assigned to agent.');
    }

    // Assign complaint ke agent
    public function assignAgent(Request $request, Complaint $complaint)
    {

        $supervisor = $request->user();
        $assignedAt = now();

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'agent_id' => ['required', 'exists:users,id'],
            'sla_resolution_deadline' => ['nullable', 'date', 'after:now'],
        ]);

        $resolutionDeadline = $request->sla_resolution_deadline
            ? \Carbon\Carbon::parse($request->sla_resolution_deadline)
            : $assignedAt->copy()->addDays(3);

        // pastikan agent memang AGENT dan satu department
        $agent = User::where('id', $validated['agent_id'])
            ->where('role', 'AGENT')
            ->where('department_id', $complaint->department_id)
            ->firstOrFail();
        $complaint->update([
            'department_id' => $validated['department_id'],
            'agent_id' => $agent->id,
            'assigned_by' => $supervisor->id,
            'status'   => 'ASSIGNED',

            // SLA start
            'assigned_at' => $assignedAt,
            'sla_response_deadline' => $assignedAt->copy()->addHours(24),
            'sla_resolution_deadline' => $resolutionDeadline,
        ]);

        // Notify agent
        NotificationService::send(
            $agent->id, 'info',
            'New Complaint Assigned',
            "Complaint #{$complaint->id} has been assigned to you.",
            route('complaints.show', $complaint)
        );

        // Notify user
        NotificationService::send(
            $complaint->user_id, 'info',
            'Complaint Assigned',
            "Your complaint #{$complaint->id} has been assigned to an agent.",
            route('complaints.show', $complaint)
        );

        return back()->with('success', 'Complaint assigned to agent.');
    }

    public function reassign(Request $request, Complaint $complaint)
    {
        $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'agent_id' => ['required', 'exists:users,id'],
            'reason' => ['required', 'string', 'min:5'],
            'sla_resolution_deadline' => ['nullable', 'date', 'after:now'],
        ]);

        $user = $request->user();

        if (!$complaint->canBeReassigned()) {
            return back()->withErrors([
                'status' => 'This complaint cannot be reassigned.'
            ]);
        }

        $resolutionDeadline = $request->sla_resolution_deadline
            ? \Carbon\Carbon::parse($request->sla_resolution_deadline)
            : now()->addDays(3);

        // Create pending reassignment
        ComplaintAssignment::create([
            'complaint_id' => $complaint->id,
            'from_agent_id' => $complaint->agent_id,
            'to_agent_id' => $request->agent_id,
            'to_department_id' => $request->department_id,
            'assigned_by' => $user->id,
            'reason' => $request->reason,
            'status' => 'PENDING',
            'sla_resolution_deadline' => $resolutionDeadline,
        ]);

        // Set complaint to pending reassign
        $complaint->update([
            'status' => 'PENDING_REASSIGN',
        ]);

        ComplaintInternalNote::create([
            'complaint_id' => $complaint->id,
            'author_id' => $user->id,
            'author_role' => $user->role,
            'note' => "Reassign requested: to Agent ID {$request->agent_id}. Awaiting confirmation from current agent. Reason: {$request->reason}",
        ]);

        // Notify current agent about reassign request
        if ($complaint->agent_id) {
            NotificationService::send(
                $complaint->agent_id, 'warning',
                'Reassign Request',
                "Supervisor requested to reassign complaint #{$complaint->id}. Please confirm or reject.",
                route('complaints.show', $complaint)
            );
        }

        return back()->with('success', 'Reassign request sent. Waiting for agent confirmation.');
    }


    public function history(Request $request)
    {
        $query = Complaint::with(['user', 'agent', 'department'])
            ->whereIn('status', ['RESOLVED', 'CLOSED']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'ilike', "%{$search}%")
                  ->orWhere('complaint_reason', 'ilike', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->department) {
            $query->where('department_id', $request->department);
        }

        $tickets = $query->latest()->paginate(20)->withQueryString();
        $departments = Department::all();

        return view('supervisor.history', compact('tickets', 'departments'));
    }

    public function sla(Request $request)
    {
        // Response SLA: assigned but no first response yet, approaching or past deadline
        $responseTickets = Complaint::with(['user', 'agent'])
            ->whereNotNull('sla_response_deadline')
            ->whereNull('first_response_at')
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where(function ($q) {
                $q->where('sla_response_deadline', '<', now()) // breached
                  ->orWhere('sla_response_deadline', '<=', now()->addHours(12)); // at risk
            })
            ->orderBy('sla_response_deadline')
            ->get();

        // Resolution SLA: active tickets with deadline approaching or past
        $resolutionTickets = Complaint::with(['user', 'agent'])
            ->whereNotNull('sla_resolution_deadline')
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where(function ($q) {
                $q->where('sla_resolution_deadline', '<', now()) // breached
                  ->orWhere('sla_resolution_deadline', '<=', now()->addHours(12)); // warning/critical
            })
            ->orderBy('sla_resolution_deadline')
            ->get();

        $metrics = [
            'response_breached' => Complaint::responseSlaBreached()->count(),
            'resolution_breached' => Complaint::resolutionSlaBreached()->count(),
            'critical' => $resolutionTickets->filter(fn($c) => $c->sla_status === 'CRITICAL')->count(),
            'warning' => $resolutionTickets->filter(fn($c) => $c->sla_status === 'WARNING')->count(),
        ];

        return view('supervisor.sla', compact('responseTickets', 'resolutionTickets', 'metrics'));
    }

}
