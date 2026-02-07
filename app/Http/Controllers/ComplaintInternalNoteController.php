<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;

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

        return redirect()
            ->back()
            ->with('success', 'Internal note added successfully.');
    }

}
