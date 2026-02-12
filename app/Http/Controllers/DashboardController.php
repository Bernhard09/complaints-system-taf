<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;

class DashboardController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user();

        $complaints = Complaint::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('user.dashboard', compact('complaints'));
    }

    public function agent(Request $request)
    {
        $user = $request->user();

        $complaints = Complaint::where('agent_id', $user->id)
            ->latest()
            ->get();

        return view('agent.dashboard', compact('complaints'));
    }

    public function supervisor()
    {
        $complaints = Complaint::latest()->get();

        return view('supervisor.dashboard', compact('complaints'));
    }
}
