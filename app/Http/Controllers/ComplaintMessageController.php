<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;

class ComplaintMessageController extends Controller
{
    public function storeUser(Request $request, Complaint $complaint)
    {
        $user = $request->user();

        // Security
        abort_if(
            $complaint->user_id !== $user->id,
            403
        );

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $complaint->messages()->create([
            'sender_id'   => $user->id,
            'sender_role' => 'USER',
            'message'     => $validated['message'],
        ]);

        // User sudah membalas → agent bisa lanjut
        $complaint->update([
            'status' => 'IN_PROGRESS',
        ]);

        return back();
    }

    public function storeAgent(Request $request, Complaint $complaint)
    {

        $user = $request->user();

        // Security
        abort_if(
            $complaint->agent_id !== $user->id,
            403
        );

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $complaint->messages()->create([
            'sender_id'   => $user->id,
            'sender_role' => 'AGENT',
            'message'     => $validated['message'],
        ]);

        // Agent menunggu user
        $complaint->update([
            'status' => 'WAITING_USER',
        ]);

        return back();
    }

}
