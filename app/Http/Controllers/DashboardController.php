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

    // ────────────────────────────────────────────────────
    //  JSON poll endpoints for real-time dashboard updates
    // ────────────────────────────────────────────────────

    public function pollUser(Request $request)
    {
        $user = $request->user();
        $complaints = Complaint::where('user_id', $user->id);

        return response()->json([
            'ongoing'  => (clone $complaints)->whereIn('status', ['SUBMITTED','ASSIGNED','IN_PROGRESS','WAITING_USER'])->count(),
            'resolved' => (clone $complaints)->where('status', 'RESOLVED')->count(),
            'waiting'  => (clone $complaints)->where('status', 'WAITING_USER')->count(),
            'total'    => $complaints->count(),
            'recent'   => Complaint::where('user_id', $user->id)
                ->latest()->take(4)->get()
                ->map(fn ($c) => [
                    'id'     => $c->id,
                    'reason' => $c->complaint_reason,
                    'status' => $c->status,
                    'date'   => $c->created_at->diffForHumans(),
                    'url'    => route('complaints.show', $c),
                ]),
        ]);
    }

    public function pollAgent(Request $request)
    {
        $user = $request->user();
        $complaints = Complaint::where('agent_id', $user->id)->get();

        return response()->json([
            'active'       => $complaints->whereIn('status', ['ASSIGNED','IN_PROGRESS','WAITING_USER'])->count(),
            'waiting'      => $complaints->where('status', 'WAITING_USER')->count(),
            'breached'     => $complaints->filter(fn ($c) => $c->isResolutionSlaBreached())->count(),
            'resolved_today' => $complaints->where('status', 'RESOLVED')
                ->filter(fn ($c) => $c->resolved_at && $c->resolved_at->isToday())->count(),
            'complaints'   => $complaints->map(fn ($c) => [
                'id'     => $c->id,
                'status' => $c->status,
            ])->values(),
        ]);
    }

    public function pollSupervisor()
    {
        $complaints = Complaint::all();

        return response()->json([
            'incoming'       => $complaints->where('status', 'SUBMITTED')->count(),
            'assigned'       => $complaints->where('status', 'ASSIGNED')->count(),
            'in_progress'    => $complaints->where('status', 'IN_PROGRESS')->count(),
            'breached'       => $complaints->filter(fn ($c) => $c->isResolutionSlaBreached())->count(),
            'resolved_today' => $complaints->where('status', 'RESOLVED')
                ->filter(fn ($c) => $c->resolved_at && $c->resolved_at->isToday())->count(),
            'complaints'     => $complaints->map(fn ($c) => [
                'id'     => $c->id,
                'status' => $c->status,
            ])->values(),
        ]);
    }

    /**
     * Poll complaint status (used on complaints.show page).
     */
    public function pollComplaintStatus(Request $request, Complaint $complaint)
    {
        $user = $request->user();
        abort_unless(
            $complaint->user_id === $user->id
            || $complaint->agent_id === $user->id
            || $user->role === 'SUPERVISOR',
            403
        );

        $complaint->refresh();

        return response()->json([
            'status'     => $complaint->status,
            'sla_status' => $complaint->sla_status,
            'agent'      => $complaint->agent ? $complaint->agent->name : null,
        ]);
    }
}
