<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;

class DashboardController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user();

        $complaints = Complaint::where('user_id', $user->id);

        $ongoing = (clone $complaints)
            ->whereIn('status', ['SUBMITTED', 'ASSIGNED', 'IN_PROGRESS', 'WAITING_USER'])
            ->count();

        $resolved = (clone $complaints)
            ->where('status', 'RESOLVED')
            ->count();

        $waiting = (clone $complaints)
            ->where('status', 'WAITING_USER')
            ->count();

        $total = $complaints->count();

        $recent = (clone $complaints)
            ->latest()
            ->take(3)
            ->get();

        return view('user.dashboard', compact(
            'ongoing',
            'resolved',
            'waiting',
            'total',
            'recent',
            'complaints'
        ));
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

        return view('supervisor.dashboard.index', compact('complaints'));
    }
}
