<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Department;
use Illuminate\Http\Request;

class SupervisorComplaintController extends Controller
{
    // List semua complaint baru
    public function index()
    {
        $complaints = Complaint::whereIn('status', ['SUBMITTED', 'IN_REVIEW'])
            ->latest()
            ->get();

        $departments = Department::all();

        return view('supervisor.complaints.index', compact('complaints', 'departments'));
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
}
