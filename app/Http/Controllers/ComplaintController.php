<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    // Form submit
    public function create()
    {
        return view('complaints.create');
    }

    // Simpan complaint
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_number' => ['required', 'digits_between:10,15'],
            'complaint_reason' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
        ]);

        Complaint::create([
            'user_id' => $request->user()->id,
            'contract_number' => $validated['contract_number'],
            'complaint_reason' => $validated['complaint_reason'],
            'description' => $validated['description'],
            'status' => 'SUBMITTED',
        ]);

        return redirect()
            ->route('complaints.create')
            ->with('success', 'Complaint submitted successfully.');
    }
    public function show(Request $request, Complaint $complaint)
    {
        $user = $request->user();

        // Security
        $isOwner = $user->role === 'USER' && $complaint->user_id === $user->id;
        $isAgent = $user->role === 'AGENT' && $complaint->agent_id === $user->id;
        $isSupervisor = $user->role === 'SUPERVISOR';

        abort_unless($isOwner || $isAgent || $isSupervisor, 403);

        $complaint->load([
            'messages.sender',
            'internalNotes.author',
        ]);

        return view('complaints.show', compact('complaint', 'user'));
    }

}
