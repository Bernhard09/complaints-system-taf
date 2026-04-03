<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Services\NotificationService;

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
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'attachment.max' => 'The attachment must not exceed 5MB.',
        ]);

        $data = [
            'sender_id'   => $user->id,
            'sender_role' => 'USER',
            'message'     => $validated['message'] ?? '',
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('chat-attachments', 's3');
            // $data['attachment_path'] = $file->store('chat-attachments', 'public');
            $data['attachment_name'] = $file->getClientOriginalName();
        }

        $complaint->messages()->create($data);

        // Notify agent about user message
        if ($complaint->agent_id) {
            NotificationService::send(
                $complaint->agent_id, 'info',
                'New Message from User',
                "User sent a message on complaint #{$complaint->id}.",
                route('complaints.show', $complaint)
            );
        }

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
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'attachment.max' => 'The attachment must not exceed 5MB.',
        ]);

        $data = [
            'sender_id'   => $user->id,
            'sender_role' => 'AGENT',
            'message'     => $validated['message'] ?? '',
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('chat-attachments', 's3');
            // $data['attachment_path'] = $file->store('chat-attachments', 'public');
            $data['attachment_name'] = $file->getClientOriginalName();
        }

        $complaint->messages()->create($data);

        // Notify user about agent message
        NotificationService::send(
            $complaint->user_id, 'info',
            'New Message from Agent',
            "Agent replied on complaint #{$complaint->id}.",
            route('complaints.show', $complaint)
        );

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

    /**
     * JSON endpoint for AJAX chat polling.
     * Returns messages newer than ?after_id.
     */
    public function poll(Request $request, Complaint $complaint)
    {
        $user = $request->user();

        // Security: user must be the complaint owner, assigned agent, or supervisor
        abort_unless(
            $complaint->user_id === $user->id
            || $complaint->agent_id === $user->id
            || $user->role === 'SUPERVISOR',
            403
        );

        $afterId = (int) $request->query('after_id', 0);

        $messages = $complaint->messages()
            ->with('sender')
            ->when($afterId, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->get()
            ->map(fn ($msg) => [
                'id'          => $msg->id,
                'sender_name' => $msg->sender->name ?? 'Unknown',
                'sender_role' => $msg->sender_role,
                'sender_id'   => $msg->sender_id,
                'message'     => $msg->message,
                'is_system'   => $msg->is_system ?? false,
                // 'attachment_path' => $msg->attachment_path ? asset('storage/' . $msg->attachment_path) : null,
                'attachment_path' => $msg->attachment_path ? \Storage::disk('s3')->url($msg->attachment_path) : null,
                'attachment_name' => $msg->attachment_name,
                'time'        => $msg->created_at->diffForHumans(),
            ]);

        return response()->json([
            'messages' => $messages,
            'status'   => $complaint->fresh()->status,
        ]);
    }
}
