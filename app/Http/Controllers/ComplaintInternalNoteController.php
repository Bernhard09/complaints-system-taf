<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Services\NotificationService;

class ComplaintInternalNoteController extends Controller
{
    public function storeAgent(Request $request, Complaint $complaint)
    {
        $user = $request->user();

        // Security
        abort_unless(
            in_array($user->role, ['SUPERVISOR', 'AGENT']),
            403
        );
        $validated = $request->validate([
            'note' => ['required', 'string'],
        ]);

        $complaint->internalNotes()->create([
            'author_id' => $user->id,
            'author_role' => $user->role,
            'note' => $validated['note'],
        ]);

        // Notify the other party
        if ($user->role === 'AGENT' && $complaint->assigned_by) {
            NotificationService::send(
                $complaint->assigned_by, 'info',
                'New Internal Note',
                "{$user->name} added an internal note on complaint #{$complaint->id}.",
                route('supervisor.complaints.show', $complaint)
            );
        } elseif ($user->role === 'SUPERVISOR' && $complaint->agent_id) {
            NotificationService::send(
                $complaint->agent_id, 'info',
                'New Internal Note',
                "{$user->name} added an internal note on complaint #{$complaint->id}.",
                route('complaints.show', $complaint)
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Internal note added successfully.');
    }

}
