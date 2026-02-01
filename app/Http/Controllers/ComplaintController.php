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
}
