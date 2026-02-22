<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use App\Models\ComplaintAssignment;
use App\Models\ComplaintInternalNote;


use Illuminate\Http\Request;

class SupervisorComplaintController extends Controller
{
    //
    public function index(Request $request)
    {

    $columns = [
            'SUBMITTED' => 'Incoming',
            'ASSIGNED' => 'Assigned',
            'IN_PROGRESS' => 'In Progress',
            'WAITING_USER' => 'Waiting User',
            'RESOLVED' => 'Resolved',
        ];

        $board = [];

        foreach ($columns as $status => $label) {
            $board[$status] = Complaint::with(['user', 'agent'])
                ->where('status', $status)
                ->latest()
                ->take(20)
                ->get();
        }

        return view('supervisor.dashboard', compact('board', 'columns'));


        // $complaints = Complaint::whereIn('status', ['SUBMITTED', 'IN_REVIEW', 'ASSIGNED'])
        //     ->latest()
        //     ->get();

        // $departments = Department::all();

        // // semua agent, nanti difilter di view
        // $agents = User::where('role', 'AGENT')->get();

        // $responseBreached = Complaint::responseSlaBreached()->pluck('id')->toArray();
        // $resolutionBreached = Complaint::resolutionSlaBreached()->pluck('id')->toArray();

        // return view(
        //     'supervisor.complaints.index',
        //     compact(
        //         'complaints',
        //         'departments',
        //         'agents',
        //         'responseBreached',
        //         'resolutionBreached')
        // );
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
        ]);

        // pastikan agent memang AGENT dan satu department
        $agent = User::where('id', $validated['agent_id'])
            ->where('role', 'AGENT')
            ->where('department_id', $complaint->department_id)
            ->firstOrFail();
        dd($agent);
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

        return back()->with('success', 'Complaint assigned to agent.');
    }

    public function reassign(Request $request, Complaint $complaint)
    {
        $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $user = $request->user();

        $oldAgent = $complaint->agent_id;

        if (!$complaint->canBeReassigned()) {
            return back()->withErrors([
                'status' => 'This complaint cannot be reassigned.'
            ]);
        }


        // Save history
        ComplaintAssignment::create([
            'complaint_id' => $complaint->id,
            'from_agent_id' => $oldAgent,
            'to_agent_id' => $request->agent_id,
            'assigned_by' => $user->id,
            'reason' => $request->reason,
        ]);

        // Reset SLA (example 24h)
        $newDeadline = now()->addHours(24);

        // Update complaint
        $complaint->update([
            'agent_id' => $request->agent_id,
            'assigned_by' => $user->id,
            'assigned_at' => now(),
            'status' => 'ASSIGNED',
            'sla_resolution_deadline' => $newDeadline,
        ]);

        ComplaintInternalNote::create([
            'complaint_id' => $complaint->id,
            'user_id' => $user->id, // supervisor
            'note' => "Complaint reassigned from Agent ID {$oldAgent} to Agent ID {$request->agent_id}. Reason: {$request->reason}",
        ]);


        return back()->with('success', 'Complaint reassigned successfully.');
    }


}
