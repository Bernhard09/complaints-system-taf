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

        if ($complaint->status === 'SUBMITTED') {
            return back()->with('error', 'Please wait until an agent is assigned.');
        }

        if ($complaint->status === 'RESOLVED') {
            return back()->with('error', 'This complaint has been resolved.');
        }

        $validated = $request->validate([
            'message' => ['required_without:attachment', 'nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $data = [
            'sender_id'   => $user->id,
            'sender_role' => 'USER',
            'message'     => $validated['message'] ?? '',
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('chat-attachments', 'public');
            $data['attachment_name'] = $file->getClientOriginalName();
        }

        $complaint->messages()->create($data);

        if ($complaint->status === 'ASSIGNED') {
            return back();
        }

        // WAITING_USER → IN_PROGRESS when user replies
        if (in_array($complaint->status, ['WAITING_USER', 'WAITING_CONFIRMATION'])) {
            $complaint->update([
                'status' => 'IN_PROGRESS',
            ]);
        }

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

        if ($complaint->status === 'RESOLVED') {
            return back()->with('error', 'This complaint has been resolved.');
        }

        $validated = $request->validate([
            'message' => ['required_without:attachment', 'nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $data = [
            'sender_id'   => $user->id,
            'sender_role' => 'AGENT',
            'message'     => $validated['message'] ?? '',
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('chat-attachments', 'public');
            $data['attachment_name'] = $file->getClientOriginalName();
        }

        $complaint->messages()->create($data);

        // First response tracking
        if ($complaint->status === 'ASSIGNED' && is_null($complaint->first_response_at)) {
            $complaint->update([
                'status' => 'IN_PROGRESS',
                'first_response_at' => now(),
            ]);
        }

        // WAITING_USER → IN_PROGRESS when agent chats
        if ($complaint->status === 'WAITING_USER') {
            $complaint->update([
                'status' => 'IN_PROGRESS',
            ]);
        }

        return back();
    }
}
