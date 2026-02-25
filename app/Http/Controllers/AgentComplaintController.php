<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

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

        // SLA Filter (FIXED)
        if ($request->sla === 'BREACHED') {
            $tableQuery
                ->whereNotNull('sla_resolution_deadline')
                ->where('sla_resolution_deadline', '<', now());
        }

        if ($request->sla === 'CRITICAL') {
            $tableQuery
                ->whereNotNull('sla_resolution_deadline')
                ->where('sla_resolution_deadline', '>=', now())
                ->where('sla_resolution_deadline', '<=', now()->addHours(4));
        }

        if ($request->sla === 'WARNING') {
            $tableQuery
                ->whereNotNull('sla_resolution_deadline')
                ->where('sla_resolution_deadline', '>', now()->addHours(4))
                ->where('sla_resolution_deadline', '<=', now()->addHours(12));
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

        $tickets = Complaint::where('agent_id', $agent->id)
            ->whereNotNull('sla_resolution_deadline')
            ->whereNotIn('status', ['RESOLVED'])
            ->with('user')
            ->orderBy('sla_resolution_deadline')
            ->get();

        return view('agent.sla', compact('tickets'));
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
