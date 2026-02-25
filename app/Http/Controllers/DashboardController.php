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
            ->take(4)
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

    public function complaints(Request $request)
    {
        $user = $request->user();

        $query = Complaint::where('user_id', $user->id)->latest();

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'ilike', "%{$search}%")
                  ->orWhere('complaint_reason', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Status filter
        if ($request->status && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        // Date range
        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $complaints = $query->paginate(12)->withQueryString();

        return view('user.complaints', compact('complaints'));
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
        $incoming = Complaint::where('status', 'SUBMITTED')->count();

        $assigned = Complaint::where('status', 'ASSIGNED')->count();

        $inProgress = Complaint::where('status', 'IN_PROGRESS')->count();

        $waitingUser = Complaint::where('status', 'WAITING_USER')->count();

        $resolvedToday = Complaint::where('status', 'RESOLVED')
            ->whereDate('updated_at', today())
            ->count();

        $overdue = Complaint::whereNotNull('sla_resolution_deadline')
            ->where('sla_resolution_deadline', '<', now())
            ->whereNotIn('status', ['RESOLVED'])
            ->count();

        return view('supervisor.dashboard', compact(
            'incoming',
            'assigned',
            'inProgress',
            'waitingUser',
            'resolvedToday',
            'overdue'
        ));
    }
}
