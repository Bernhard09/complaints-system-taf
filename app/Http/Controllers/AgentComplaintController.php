<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class AgentComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::where('agent_id', $request->user()->id)
            ->where('status', 'IN_PROGRESS')
            ->latest()
            ->get();

        return view('agent.complaints.index', compact('complaints'));
    }
}
