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

        if ($complaint->status === 'SUBMITTED' ) {
            return back()->with('error', 'Please wait until an agent is assigned.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $complaint->messages()->create([
            'sender_id'   => $user->id,
            'sender_role' => 'USER',
            'message'     => $validated['message'],
        ]);


        if ($complaint->status === 'ASSIGNED' ) {
            return back();
        }

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

        // Jika status masih ASSIGNED, ubah ke IN_PROGRESS
        if ($complaint->status === 'ASSIGNED' && is_null($complaint->first_response_at)) {
            $complaint->update([
                'status' => 'IN_PROGRESS',
                'first_response_at' => now(),
            ]);
        }

        return back();
    }

}
