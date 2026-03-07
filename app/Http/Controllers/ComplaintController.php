<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;

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
        $user = $request->user();

        $validated = $request->validate([
            'contract_number' => ['required', 'digits_between:10,15'],
            'complaint_reason' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],

            // Attachment validation
            'attachments' => [
                'nullable',
                'array',
                'max:10',
                function ($attribute, $value, $fail) {
                    $totalSize = array_reduce($value, function ($carry, $file) {
                        return $carry + $file->getSize();
                    }, 0);
                    
                    if ($totalSize > 5120 * 1024) { // 5MB in bytes
                        $fail('The total size of all attachments must not exceed 5MB.');
                    }
                },
            ],
            'attachments.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
            ],
        ], [
            // removed per-file size custom message
        ]);

        // Duplicate active complaint check
        $activeExists = Complaint::where('user_id', $user->id)
            ->where('contract_number', $validated['contract_number'])
            ->whereIn('status', [
                'SUBMITTED',
                'ASSIGNED',
                'IN_PROGRESS',
                'WAITING_USER',
            ])
            ->exists();

        if ($activeExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'contract_number' =>
                        'There is already an active complaint for this contract.',
                ]);
        }

        DB::beginTransaction();

        try {

            //  Create complaint
            $complaint = Complaint::create([
                'user_id' => $user->id,
                'contract_number' => $validated['contract_number'],
                'complaint_reason' => $validated['complaint_reason'],
                'description' => $validated['description'],
                'status' => 'SUBMITTED',
            ]);

            $storedFiles = [];

            // Handle attachments
            if ($request->hasFile('attachments')) {

                foreach ($request->file('attachments') as $file) {

                    $path = $file->store('complaint-attachments', 'public');

                    $storedFiles[] = $path;

                    $complaint->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);

                }
            }

            DB::commit();

            // Notify user
            NotificationService::send(
                $user->id, 'success',
                'Complaint Submitted',
                "Your complaint #{$complaint->id} has been submitted successfully.",
                route('complaints.show', $complaint)
            );

            // Notify all supervisors
            NotificationService::sendToRole(
                'SUPERVISOR', 'info',
                'New Complaint Submitted',
                "Complaint #{$complaint->id} submitted by {$user->name}. Needs assignment.",
                route('supervisor.complaints.show', $complaint)
            );

            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', 'Complaint submitted successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

        // Cleanup files if any stored
            if (!empty($storedFiles)) {
                foreach ($storedFiles as $filePath) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            return back()
                ->withInput()
                ->withErrors('Something went wrong. Please try again.');
        }
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
            'attachments',
        ]);

        return view('complaints.show', compact('complaint', 'user'));
    }

    public function confirmResolution(Request $request, Complaint $complaint)
    {
        $user = $request->user();

        abort_unless(
            $user->role === 'USER'
            && $complaint->user_id === $user->id
            && $complaint->status === 'WAITING_CONFIRMATION',
            403
        );

        $complaint->update([
            'status' => 'RESOLVED',
            'resolved_at' => now(),
        ]);

        // Notify agent
        if ($complaint->agent_id) {
            NotificationService::send(
                $complaint->agent_id, 'success',
                'Complaint Resolved',
                "User confirmed resolution for complaint #{$complaint->id}.",
                route('complaints.show', $complaint)
            );
        }

        // Notify supervisors
        NotificationService::sendToRole(
            'SUPERVISOR', 'success',
            'Complaint Resolved',
            "Complaint #{$complaint->id} has been resolved.",
            route('supervisor.complaints.show', $complaint)
        );

        return back()->with('success', 'Complaint has been closed. Thank you.');
    }

    public function rejectResolution(Request $request, Complaint $complaint)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $user = $request->user();

        abort_unless(
            $user->role === 'USER'
            && $complaint->user_id === $user->id
            && $complaint->status === 'WAITING_CONFIRMATION',
            403
        );

        $complaint->update([
            'status' => 'IN_PROGRESS',
        ]);

        // Add internal note about rejection
        $complaint->internalNotes()->create([
            'author_id' => $user->id,
            'note' => "USER REJECTED RESOLUTION. Reason: " . $request->reason,
        ]);

        $complaint->messages()->create([
            'sender_id' => $user->id,
            'sender_role' => 'USER',
            'message' => "I have rejected the resolution. Reason: " . $request->reason,
            'is_system' => true,
        ]);

        // Notify agent
        if ($complaint->agent_id) {
            NotificationService::send(
                $complaint->agent_id, 'error',
                'Resolution Rejected',
                "User rejected resolution for complaint #{$complaint->id}.",
                route('complaints.show', $complaint)
            );
        }

        return back()->with('success', 'Complaint resolution has been rejected. The agent will review your concern.');
    }

    public function cancel(Request $request, Complaint $complaint)
    {
        $user = $request->user();
        abort_unless(
            $user->id === $complaint->user_id
            && $complaint->status === 'SUBMITTED',
            403
        );

        $complaint->update([
            'status' => 'CANCELLED',
        ]);

        return redirect()
            ->route('user.dashboard')
            ->with('success', 'Complaint cancelled.');
    }


}
