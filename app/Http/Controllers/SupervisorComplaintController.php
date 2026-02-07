<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;

use Illuminate\Http\Request;

class SupervisorComplaintController extends Controller
{
    // List semua complaint baru
public function index()
{
    $complaints = Complaint::whereIn('status', ['SUBMITTED', 'IN_REVIEW', 'ASSIGNED'])
        ->latest()
        ->get();

    $departments = Department::all();

    // semua agent, nanti difilter di view
    $agents = User::where('role', 'AGENT')->get();

    return view(
        'supervisor.complaints.index',
        compact('complaints', 'departments', 'agents')
    );
}



    // Assign complaint ke department
    public function assign(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
        ]);

        $complaint->update([
            'department_id' => $validated['department_id'],
            'status' => 'ASSIGNED',
        ]);

        return back()->with('success', 'Complaint assigned to department.');
    }

    // Assign complaint ke agent
    public function assignAgent(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
        ]);

        // pastikan agent memang AGENT dan satu department
        $agent = User::where('id', $validated['agent_id'])
            ->where('role', 'AGENT')
            ->where('department_id', $complaint->department_id)
            ->firstOrFail();

        $complaint->update([
            'agent_id' => $agent->id,
            'status'   => 'IN_PROGRESS',
        ]);

        return back()->with('success', 'Complaint assigned to agent.');
    }

}
