<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class AgentComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::where('agent_id', $request->user()->id)
            ->whereIn('status', ['IN_PROGRESS', 'WAITING_USER', 'WAITING_CONFIRMATION'])
            ->latest()
            ->get();

        return view('agent.complaints.index', compact('complaints'));
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

}
