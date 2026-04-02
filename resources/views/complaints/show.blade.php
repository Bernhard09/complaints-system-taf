@php
    $user = auth()->user();
    $status = $complaint->status;
    $isResolved = in_array($status, ['RESOLVED', 'CLOSED', 'CANCELLED']);
    $canChat = in_array($user->role, ['USER','AGENT'])
        && !in_array($status, ['SUBMITTED','RESOLVED','CLOSED','CANCELLED','PENDING_REASSIGN']);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()"
               class="text-gray-400 hover:text-gray-700 transition">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <h2 class="text-xl font-semibold">
                Complaint #{{ $complaint->id }}
            </h2>
        </div>
    </x-slot>

    <div class="mx-auto w-full max-w-screen-2xl py-6 sm:py-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ===================================== --}}
            {{-- LEFT SIDE : CONVERSATION --}}
            {{-- ===================================== --}}
            <div class="lg:col-span-2 space-y-6">

                <x-ui.card class="p-6">

                    <h3 class="font-semibold mb-4">{{ __('Conversation') }}</h3>

                    <div id="chatBox"
                            class="h-[65vh] overflow-y-auto space-y-4 pr-2 scroll-smooth">

                        @if($complaint->status === 'SUBMITTED')
                            <div class="text-gray-500 text-center h-full flex flex-col items-center justify-center">
                                <p class="text-md">Waiting for agent assignment...</p>
                                <p class="text-xs">You will be notified once assigned.</p>
                            </div>
                        @else

                            @foreach($complaint->messages as $msg)
                                @if($msg->is_system)
                                    {{-- System message --}}
                                    <div class="flex justify-center">
                                        <div class="bg-gray-200 text-gray-600 px-4 py-2 rounded-full text-xs max-w-[80%] text-center">
                                            {{ $msg->message }}
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $isMine = $msg->sender_id === auth()->id();
                                        $isUserMsg = $msg->sender_role === 'USER';
                                        // User = indigo, Agent = blue
                                        $bubbleColor = $isUserMsg
                                            ? ($isMine ? 'bg-indigo-600 text-white' : 'bg-indigo-100 text-indigo-900')
                                            : ($isMine ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-900');
                                    @endphp
                                    <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">

                                        <div class="max-w-[70%] px-4 py-3 rounded-2xl {{ $bubbleColor }}">

                                            {{-- Sender name --}}
                                            <p class="text-[10px] font-semibold mb-1 opacity-80">
                                                {{ $msg->sender->name ?? 'Unknown' }}
                                                @if($msg->sender_role === 'AGENT')
                                                    <span class="opacity-60">· Agent</span>
                                                @endif
                                            </p>

                                            @if($msg->message)
                                                <p class="text-sm whitespace-pre-wrap break-words">{{ $msg->message }}</p>
                                            @endif

                                            {{-- Attachment --}}
                                            @if($msg->attachment_path)
                                                @php
                                                    $ext = pathinfo($msg->attachment_name, PATHINFO_EXTENSION);
                                                    $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','webp']);
                                                @endphp
                                                <div class="mt-2">
                                                    @if($isImage)
                                                        <a href="{{ asset('storage/' . $msg->attachment_path) }}"
                                                           target="_blank">
                                                            <img src="{{ asset('storage/' . $msg->attachment_path) }}"
                                                                 alt="{{ $msg->attachment_name }}"
                                                                 class="rounded-lg max-h-48 cursor-pointer hover:opacity-90 transition" />
                                                        </a>
                                                    @else
                                                        <a href="{{ asset('storage/' . $msg->attachment_path) }}"
                                                           target="_blank"
                                                           class="inline-flex items-center gap-1 text-xs underline opacity-80 hover:opacity-100">
                                                            📎 {{ $msg->attachment_name }}
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif

                                            <p class="text-[10px] mt-2 opacity-70">
                                                {{ $msg->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                        @endif
                    </div>

                    {{-- Pending Reassign Confirm/Reject (for old agent) --}}
                    @if($complaint->status === 'PENDING_REASSIGN' && $user->role === 'AGENT' && $complaint->agent_id === $user->id)
                        @php $pendingAssign = $complaint->pendingReassignment(); @endphp
                        @if($pendingAssign)
                            <div x-data="{ showReject: false }" class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4">
                                <p class="text-sm font-semibold text-amber-700">⏳ Reassign Request</p>
                                <p class="text-xs text-amber-600 mt-1">
                                    Supervisor wants to reassign this complaint to another agent.
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Reason: {{ $pendingAssign->reason }}</p>

                                <div class="flex gap-2 mt-3">
                                    <form method="POST" action="{{ route('agent.reassign.confirm', $pendingAssign) }}">
                                        @csrf
                                        <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                                            ✓ Confirm
                                        </button>
                                    </form>

                                    <button @click="showReject = !showReject"
                                            class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                                        ✕ Reject
                                    </button>
                                </div>

                                <div x-show="showReject" x-transition class="mt-3">
                                    <form method="POST" action="{{ route('agent.reassign.reject', $pendingAssign) }}">
                                        @csrf
                                        <textarea name="rejection_reason" rows="2" required
                                                  placeholder="Why are you rejecting this reassign?"
                                                  class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                                        <button class="mt-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                                            Submit Rejection
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @elseif($complaint->status === 'PENDING_REASSIGN')
                        <div class="mt-4 text-sm text-amber-600 italic">
                            ⏳ Reassign pending — waiting for agent confirmation
                        </div>
                    @elseif($isResolved)
                        <div class="mt-4 text-sm text-gray-400 italic text-center py-2 border-t">
                            This complaint has been resolved. Chat is closed.
                        </div>
                    @elseif($canChat)
                        <form method="POST"
                              action="{{ route('complaints.messages.'.strtolower($user->role), $complaint) }}"
                              enctype="multipart/form-data"
                              class="mt-4"
                              x-data="{ fileName: '' }">
                            @csrf

                            {{-- Attachment preview --}}
                            <div x-show="fileName" x-transition
                                 class="mb-2 flex items-center gap-2 text-xs text-gray-500 bg-gray-50 px-3 py-2 rounded-lg">
                                <span>📎</span>
                                <span x-text="fileName" class="truncate"></span>
                                <button type="button"
                                        @click="fileName = ''; $refs.fileInput.value = ''"
                                        class="ml-auto text-red-400 hover:text-red-600">✕</button>
                            </div>

                            <div class="flex gap-3 items-center">
                                {{-- Attachment button --}}
                                <label class="cursor-pointer text-gray-400 hover:text-indigo-600 transition">
                                    <x-heroicon-o-paper-clip class="w-5 h-5" />
                                    <input type="file"
                                           name="attachment"
                                           x-ref="fileInput"
                                           @change="fileName = $event.target.files[0]?.name || ''"
                                           accept="image/*,.pdf"
                                           class="hidden" />
                                </label>

                                <input name="message"
                                       class="flex-1 border rounded-xl px-4 py-2"
                                       placeholder="Type your message..."
                                />

                                <x-ui.button>{{ __('Send') }}</x-ui.button>
                            </div>
                        </form>
                    @elseif($user->role === 'SUPERVISOR')
                        <div class="mt-4 text-sm text-gray-500 italic">
                            Supervisor view (read-only)
                        </div>
                    @endif

                </x-ui.card>

            </div>

            {{-- ===================================== --}}
            {{-- RIGHT SIDE : SIDEBAR --}}
            {{-- ===================================== --}}
            <div class="space-y-6">

                <div class="sticky top-6 space-y-6">

                    {{-- SLA --}}
                    @if($complaint->sla_response_deadline || $complaint->sla_resolution_deadline)
                        <x-ui.card class="p-6">
                            <h3 class="font-semibold mb-4">{{ __('SLA') }}</h3>

                            <div class="space-y-4 text-sm">

                                {{-- Response SLA --}}
                                @if($complaint->sla_response_deadline)
                                    <div id="sla-response-section">
                                        <p class="text-gray-400 text-xs uppercase mb-1">Response SLA</p>
                                        @if($complaint->first_response_at)
                                            <p class="text-green-600 font-medium" id="sla-response-text">
                                                ✓ Responded {{ $complaint->first_response_at->format('d M Y H:i') }}
                                            </p>
                                            <p class="text-xs text-gray-400" id="sla-response-diff">
                                                {{ $complaint->first_response_at->diffForHumans($complaint->assigned_at) }} after assignment
                                            </p>
                                        @else
                                            @php $respBreached = now()->greaterThan($complaint->sla_response_deadline); @endphp
                                            <p class="font-semibold {{ $respBreached ? 'text-red-600' : 'text-gray-700' }}" id="sla-response-deadline">
                                                {{ $complaint->sla_response_deadline->format('d M Y H:i') }}
                                            </p>
                                            <span id="sla-response-badge" class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $respBreached ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600' }}">
                                                {{ $respBreached ? 'BREACHED' : $complaint->sla_response_deadline->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Resolution SLA --}}
                                @if($complaint->sla_resolution_deadline)
                                    <div id="sla-resolution-section">
                                        <p class="text-gray-400 text-xs uppercase mb-1">Resolution SLA</p>
                                        @if(in_array($complaint->status, ['RESOLVED', 'CLOSED']))
                                            <p class="text-green-600 font-medium" id="sla-resolution-text">
                                                ✓ Resolved {{ $complaint->resolved_at ? $complaint->resolved_at->diffForHumans() : '' }}
                                            </p>
                                        @else
                                            @php
                                                $resDeadline = $complaint->sla_resolution_deadline;
                                                $slaStatus = $complaint->sla_status;
                                                $slaColor = match($slaStatus) {
                                                    'BREACHED' => 'text-red-600',
                                                    'CRITICAL' => 'text-orange-600',
                                                    'WARNING'  => 'text-yellow-600',
                                                    default    => 'text-gray-700',
                                                };
                                                $badgeClass = match($slaStatus) {
                                                    'BREACHED' => 'bg-red-100 text-red-600',
                                                    'CRITICAL' => 'bg-orange-100 text-orange-600',
                                                    'WARNING'  => 'bg-yellow-100 text-yellow-600',
                                                    default    => 'bg-green-100 text-green-600',
                                                };
                                            @endphp
                                            <p class="font-semibold {{ $slaColor }}" id="sla-resolution-deadline">
                                                {{ $resDeadline->format('d M Y H:i') }}
                                            </p>
                                            <span id="sla-resolution-badge" class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                {{ $slaStatus }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                            </div>
                        </x-ui.card>
                    @endif

                    {{-- Complaint Info --}}
                    <x-ui.card class="p-6">
                        <h3 class="font-semibold mb-4">Complaint Info</h3>

                        <div class="space-y-4 text-sm">

                            <div>
                                <p class="text-gray-400 text-xs uppercase">Ticket</p>
                                <p class="font-semibold">#{{ $complaint->id }}</p>
                            </div>

                            <div>
                                <p class="text-gray-400 text-xs uppercase">Contract</p>
                                <p>{{ $complaint->contract_number }}</p>
                            </div>

                            <div>
                                <p class="text-gray-400 text-xs uppercase">Status</p>
                                <span id="complaint-status-badge" data-current-status="{{ $complaint->status }}">
                                    <x-ui.status-badge :status="$complaint->status" />
                                </span>
                            </div>

                            <div>
                                <p class="text-gray-400 text-xs uppercase">Reason</p>
                                <p>{{ $complaint->complaint_reason }}</p>
                            </div>

                            <div>
                                <p class="text-gray-400 text-xs uppercase">Description</p>
                                <p class="text-gray-600 break-words whitespace-pre-wrap">{{ $complaint->description }}</p>
                            </div>

                            {{-- Complaint Attachments (from submission) --}}
                            @if($complaint->attachments && $complaint->attachments->count())
                                <div>
                                    <p class="text-gray-400 text-xs uppercase mb-2">Attachments</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($complaint->attachments as $attachment)
                                            @php
                                                $ext = pathinfo($attachment->file_name, PATHINFO_EXTENSION);
                                                $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','webp']);
                                            @endphp
                                            @if($isImage)
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                   target="_blank">
                                                    <img src="{{ asset('storage/' . $attachment->file_path) }}"
                                                         alt="{{ $attachment->file_name }}"
                                                         class="rounded-lg h-24 w-full object-cover border hover:opacity-90 transition" />
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                   target="_blank"
                                                   class="flex items-center gap-1 text-xs text-indigo-600 hover:underline border rounded-lg p-2">
                                                    📎 {{ $attachment->file_name }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </x-ui.card>

                    {{-- Assigned Agent --}}
                    <x-ui.card class="p-6">
                        <h3 class="font-semibold mb-4">Assigned Agent</h3>

                        @if($complaint->status === 'SUBMITTED')
                            <p class="text-amber-600 text-sm">
                                Waiting for assignment
                            </p>
                        @else
                            <div class="space-y-3 text-sm">
                                <div>
                                    <p class="text-gray-400 text-xs uppercase">{{ __('Name') }}</p>
                                    <p class="font-semibold">
                                        {{ $complaint->agent->name ?? '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-gray-400 text-xs uppercase">{{ __('Department') }}</p>
                                    <p>
                                        {{ $complaint->agent->department->name ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </x-ui.card>

                    {{-- Activity --}}
                    <x-ui.card class="p-6">
                        <h3 class="font-semibold mb-4">{{ __('Activity') }}</h3>

                        <div class="space-y-3 text-sm">

                            @php
                                $activities = collect();

                                // Submitted
                                $activities->push((object)[
                                    'time' => $complaint->created_at,
                                    'type' => 'submitted',
                                ]);

                                // Assigned
                                if($complaint->assigned_at) {
                                    $activities->push((object)[
                                        'time' => $complaint->assigned_at,
                                        'type' => 'assigned',
                                    ]);
                                }

                                // Reassignments
                                foreach($complaint->assignments()->with(['fromAgent', 'toAgent', 'toDepartment', 'assignedByUser'])->get() as $assign) {
                                    $activities->push((object)[
                                        'time' => $assign->created_at,
                                        'type' => 'reassign',
                                        'assign' => $assign
                                    ]);
                                }

                                // First Response
                                if($complaint->first_response_at) {
                                    $activities->push((object)[
                                        'time' => $complaint->first_response_at,
                                        'type' => 'first_response',
                                    ]);
                                }

                                // Resolved
                                if($complaint->resolved_at) {
                                    $activities->push((object)[
                                        'time' => $complaint->resolved_at,
                                        'type' => 'resolved',
                                    ]);
                                }

                                // Response SLA Breached
                                if($complaint->sla_response_deadline && 
                                   $complaint->sla_response_deadline->isPast() && 
                                   (!$complaint->first_response_at || $complaint->first_response_at->greaterThan($complaint->sla_response_deadline))) {
                                    $activities->push((object)[
                                        'time' => $complaint->sla_response_deadline,
                                        'type' => 'response_breached',
                                    ]);
                                }

                                // Resolution SLA Breached
                                if($complaint->sla_resolution_deadline && 
                                   $complaint->sla_resolution_deadline->isPast() && 
                                   (!$complaint->resolved_at || $complaint->resolved_at->greaterThan($complaint->sla_resolution_deadline))) {
                                    $activities->push((object)[
                                        'time' => $complaint->sla_resolution_deadline,
                                        'type' => 'resolution_breached',
                                    ]);
                                }

                                $activities = $activities->sortBy('time')->values();
                            @endphp

                            @foreach($activities as $act)

                                @if($act->type === 'submitted')
                                    <div class="flex items-start gap-2">
                                        <div class="w-2 h-2 rounded-full bg-gray-400 mt-1.5 shrink-0"></div>
                                        <div>
                                            <p>Complaint submitted by <span class="font-medium">{{ $complaint->user->name }}</span></p>
                                            <p class="text-xs text-gray-400">{{ $act->time->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>

                                @elseif($act->type === 'assigned')
                                    <div class="flex items-start gap-2">
                                        <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                                        <div>
                                            <p>Assigned to <span class="font-medium">{{ $complaint->agent?->name ?? 'Agent' }}</span></p>
                                            <p class="text-xs text-gray-400">{{ $act->time->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>

                                @elseif($act->type === 'reassign')
                                    <div class="flex items-start gap-2">
                                        @if($act->assign->status === 'CONFIRMED')
                                            <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                                        @elseif($act->assign->status === 'REJECTED')
                                            <div class="w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0"></div>
                                        @else
                                            <div class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
                                        @endif
                                        <div>
                                            <p>
                                                Reassign
                                                <span class="font-medium">{{ $act->assign->fromAgent?->name }}</span>
                                                → <span class="font-medium">{{ $act->assign->toAgent?->name }}</span>
                                                ({{ $act->assign->toDepartment?->name ?? 'Same dept' }})
                                            </p>
                                            <p class="text-xs text-gray-500">By: {{ $act->assign->assignedByUser?->name }}</p>
                                            <p class="text-xs text-gray-500">Reason: {{ $act->assign->reason }}</p>

                                            @if($act->assign->status === 'CONFIRMED')
                                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-600">Confirmed</span>
                                            @elseif($act->assign->status === 'REJECTED')
                                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-600">Rejected</span>
                                                <p class="text-xs text-red-500 mt-1">{{ $act->assign->rejection_reason }}</p>
                                            @else
                                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-600">Pending</span>
                                            @endif

                                            <p class="text-xs text-gray-400">{{ $act->time->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>

                                @elseif($act->type === 'first_response')
                                    <div class="flex items-start gap-2">
                                        <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                                        <div>
                                            <p>Agent first response</p>
                                            <p class="text-xs text-gray-400">{{ $act->time->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>
                                    
                                @elseif($act->type === 'response_breached')
                                    <div class="flex items-start gap-2">
                                        <div class="w-2 h-2 rounded-full bg-red-600 mt-1.5 shrink-0"></div>
                                        <div>
                                            <p class="text-red-600 font-medium">Response SLA Breached</p>
                                            <p class="text-xs text-gray-400">{{ $act->time->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>

                                @elseif($act->type === 'resolution_breached')
                                    <div class="flex items-start gap-2">
                                        <div class="w-2 h-2 rounded-full bg-red-600 mt-1.5 shrink-0"></div>
                                        <div>
                                            <p class="text-red-600 font-medium">Resolution SLA Breached</p>
                                            <p class="text-xs text-gray-400">{{ $act->time->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>

                                @elseif($act->type === 'resolved')
                                    <div class="flex items-start gap-2">
                                        <div class="w-2 h-2 rounded-full bg-green-600 mt-1.5 shrink-0"></div>
                                        <div>
                                            <p>Complaint resolved</p>
                                            <p class="text-xs text-gray-400">{{ $act->time->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>

                                @endif

                            @endforeach

                        </div>
                    </x-ui.card>

                    {{-- Actions --}}
                    @if(in_array($user->role, ['USER', 'AGENT']))
                        <x-ui.card class="p-6">
                            <h3 class="font-semibold mb-4">{{ __('Actions') }}</h3>

                            <div class="space-y-3">

                                {{-- USER actions: Confirm/Reject resolution --}}
                                @if($user->role === 'USER')
                                    @if($status === 'WAITING_CONFIRMATION' && !$isResolved)
                                        <div x-data="{ showRejectModal: false }" class="space-y-3">
                                            <form method="POST"
                                                    action="{{ route('complaints.confirm', $complaint) }}">
                                                @csrf
                                                <button class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                                                    ✓ Confirm Resolved
                                                </button>
                                            </form>

                                            <button @click="showRejectModal = !showRejectModal"
                                                    class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
                                                ✕ Reject Resolution
                                            </button>

                                            {{-- Reject Modal / Inline Form --}}
                                            <div x-show="showRejectModal" x-transition class="mt-3 bg-red-50 p-4 rounded-xl border border-red-200">
                                                <form method="POST" action="{{ route('complaints.reject', $complaint) }}">
                                                    @csrf
                                                    <label class="block text-xs font-semibold text-red-700 mb-1">Reason for Rejection</label>
                                                    <textarea name="reason" rows="3" required
                                                              placeholder="Please explain why you are rejecting the resolution..."
                                                              class="w-full border-red-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500"></textarea>
                                                    <div class="flex gap-2 mt-3 justify-end">
                                                        <button type="button" @click="showRejectModal = false" class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                                                        <button type="submit" class="bg-red-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-red-700">Submit Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <div class="space-y-3">
                                            <button disabled class="w-full bg-green-600/50 text-white/50 py-2 rounded-lg cursor-not-allowed">
                                                ✓ Confirm Resolved
                                            </button>
                                            <button disabled class="w-full bg-red-600/50 text-white/50 py-2 rounded-lg cursor-not-allowed">
                                                ✕ Reject Resolution
                                            </button>
                                        </div>
                                    @endif
                                @endif

                                {{-- AGENT actions --}}
                                @if($user->role === 'AGENT')

                                    @if(in_array($status, ['ASSIGNED', 'IN_PROGRESS']) && !$isResolved && !in_array($status, ['PENDING_REASSIGN']))
                                        <form method="POST"
                                                action="{{ route('agent.complaints.waiting', $complaint) }}">
                                            @csrf
                                            <button class="w-full bg-yellow-500 text-white py-2 rounded-lg hover:bg-yellow-600 transition">
                                                ⏸ Mark Waiting User
                                            </button>
                                        </form>
                                    @else
                                        <button disabled class="w-full bg-yellow-500/50 text-white/50 py-2 rounded-lg cursor-not-allowed">
                                            ⏸ Mark Waiting User
                                        </button>
                                    @endif

                                    @if(in_array($status, ['IN_PROGRESS', 'WAITING_USER']) && !$isResolved && !in_array($status, ['PENDING_REASSIGN']))
                                        <form method="POST"
                                                action="{{ route('agent.complaints.requestConfirmation', $complaint) }}">
                                            @csrf
                                            <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                                                ✓ Request Resolved
                                            </button>
                                        </form>
                                    @else
                                        <button disabled class="w-full bg-blue-600/50 text-white/50 py-2 rounded-lg cursor-not-allowed">
                                            ✓ Request Resolved
                                        </button>
                                    @endif

                                @endif

                            </div>
                        </x-ui.card>
                    @endif

                    {{-- Internal Notes --}}
                    @if(in_array($user->role, ['AGENT','SUPERVISOR']))
                        <x-ui.card class="p-6">
                            <h3 class="font-semibold mb-4">{{ __('Internal Notes') }}</h3>

                            <div class="space-y-3 max-h-60 overflow-y-auto text-sm">

                                @foreach($complaint->internalNotes as $note)
                                    <div class="border-b pb-2">
                                        <p class="break-words whitespace-pre-wrap">{{ $note->note }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $note->author->name }}
                                            • {{ $note->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                @endforeach

                            </div>

                            @if(!$isResolved)
                                <form method="POST"
                                        action="{{ route('complaints.internal-notes.store', $complaint) }}"
                                        class="mt-4 flex gap-2">
                                    @csrf
                                    <input name="note"
                                            class="flex-1 border rounded-lg px-3 py-2"
                                            placeholder="Add internal note..."
                                            required>
                                    <button class="bg-yellow-600 text-white px-4 rounded-lg">
                                        Add
                                    </button>
                                </form>
                            @endif
                        </x-ui.card>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const chatBox = document.getElementById("chatBox");
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // AJAX Chat Polling
        const complaintId = {{ $complaint->id }};
        const currentUserId = {{ auth()->id() }};
        let lastMsgId = 0;

        // Find the highest existing message ID from rendered messages
        const existingBubbles = chatBox ? chatBox.querySelectorAll('[data-msg-id]') : [];
        existingBubbles.forEach(el => {
            const id = parseInt(el.dataset.msgId);
            if (id > lastMsgId) lastMsgId = id;
        });

        // If no data-msg-id elements exist, get count of messages
        @if($complaint->messages->count() > 0)
            if (lastMsgId === 0) lastMsgId = {{ $complaint->messages->last()->id ?? 0 }};
        @endif

        async function pollMessages() {
            try {
                const resp = await fetch(`/complaints/${complaintId}/messages/poll?after_id=${lastMsgId}`);
                if (!resp.ok) return;
                const data = await resp.json();

                if (data.messages.length > 0) {
                    const wasAtBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 100;

                    data.messages.forEach(msg => {
                        if (msg.id <= lastMsgId) return;

                        if (msg.is_system) {
                            // System message
                            const div = document.createElement('div');
                            div.className = 'flex justify-center';
                            div.dataset.msgId = msg.id;
                            div.innerHTML = `<div class="bg-gray-200 text-gray-600 px-4 py-2 rounded-full text-xs max-w-[80%] text-center">${escapeHtml(msg.message)}</div>`;
                            chatBox.appendChild(div);
                        } else {
                            const isMine = msg.sender_id === currentUserId;
                            const isUserMsg = msg.sender_role === 'USER';

                            let bubbleColor;
                            if (isUserMsg) {
                                bubbleColor = isMine ? 'bg-indigo-600 text-white' : 'bg-indigo-100 text-indigo-900';
                            } else {
                                bubbleColor = isMine ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-900';
                            }

                            const wrapper = document.createElement('div');
                            wrapper.className = `flex ${isMine ? 'justify-end' : 'justify-start'}`;
                            wrapper.dataset.msgId = msg.id;

                            let attachmentHtml = '';
                            if (msg.attachment_path) {
                                const ext = (msg.attachment_name || '').split('.').pop().toLowerCase();
                                const isImage = ['jpg','jpeg','png','webp'].includes(ext);
                                if (isImage) {
                                    attachmentHtml = `<div class="mt-2"><a href="${msg.attachment_path}" target="_blank"><img src="${msg.attachment_path}" alt="${escapeHtml(msg.attachment_name)}" class="rounded-lg max-h-48 cursor-pointer hover:opacity-90 transition" /></a></div>`;
                                } else {
                                    attachmentHtml = `<div class="mt-2"><a href="${msg.attachment_path}" target="_blank" class="inline-flex items-center gap-1 text-xs underline opacity-80 hover:opacity-100">📎 ${escapeHtml(msg.attachment_name)}</a></div>`;
                                }
                            }

                            const roleLabel = msg.sender_role === 'AGENT' ? '<span class="opacity-60">· Agent</span>' : '';

                            wrapper.innerHTML = `
                                <div class="max-w-[70%] px-4 py-3 rounded-2xl ${bubbleColor}">
                                    <p class="text-[10px] font-semibold mb-1 opacity-80">${escapeHtml(msg.sender_name)} ${roleLabel}</p>
                                    ${msg.message ? `<p class="text-sm whitespace-pre-wrap break-words">${escapeHtml(msg.message)}</p>` : ''}
                                    ${attachmentHtml}
                                    <p class="text-[10px] mt-2 opacity-70">${msg.time}</p>
                                </div>
                            `;

                            chatBox.appendChild(wrapper);
                        }

                        lastMsgId = msg.id;
                    });

                    // Auto-scroll if user was near bottom
                    if (wasAtBottom) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                }
            } catch (e) {
                // Silently fail
            }
        }

        // Poll every 5 seconds
        setInterval(pollMessages, 5000);

        // Complaint status polling — update badge inline every 5s
        const statusBadge = document.getElementById('complaint-status-badge');
        if (statusBadge) {
            let currentStatus = statusBadge.dataset.currentStatus;

            const statusTranslations = {{ Js::from([
                'SUBMITTED' => __('SUBMITTED'),
                'ASSIGNED' => __('ASSIGNED'),
                'IN_PROGRESS' => __('IN_PROGRESS'),
                'WAITING_USER' => __('WAITING_USER'),
                'WAITING_CONFIRMATION' => __('WAITING_CONFIRMATION'),
                'RESOLVED' => __('RESOLVED'),
                'PENDING_REASSIGN' => __('PENDING_REASSIGN'),
                'CLOSED' => __('CLOSED'),
                'CANCELLED' => __('CANCELLED'),
            ]) }};

            const statusColors = {
                'IN_PROGRESS':   'bg-indigo-100 text-indigo-700',
                'WAITING_USER':  'bg-amber-100 text-amber-700',
                'WAITING_CONFIRMATION': 'bg-purple-100 text-purple-700',
                'RESOLVED':      'bg-green-100 text-green-700',
                'ASSIGNED':      'bg-gray-100 text-gray-600',
                'SUBMITTED':     'bg-gray-100 text-gray-600',
                'PENDING_REASSIGN': 'bg-orange-100 text-orange-700',
                'CLOSED':        'bg-green-100 text-green-700',
                'CANCELLED':     'bg-red-100 text-red-700',
            };

            async function pollStatus() {
                try {
                    const resp = await fetch(`/api/poll/complaint/${complaintId}/status`);
                    if (!resp.ok) return;
                    const data = await resp.json();
                    if (data.status && data.status !== currentStatus) {
                        // Update badge inline
                        const badgeSpan = statusBadge.querySelector('span');
                        if (badgeSpan) {
                            badgeSpan.className = 'px-3 py-1 rounded-full text-xs font-medium ' + (statusColors[data.status] || 'bg-gray-100 text-gray-600');
                            badgeSpan.textContent = statusTranslations[data.status] || data.status.replace(/_/g, ' ');
                        }
                        statusBadge.dataset.currentStatus = data.status;

                        // Flash the badge then reload to update action buttons/forms
                        statusBadge.style.transition = 'transform .3s';
                        statusBadge.style.transform = 'scale(1.15)';
                        setTimeout(() => window.location.reload(), 800);

                        currentStatus = data.status;
                    }
                } catch (e) {}
            }
            // Poll immediately, then every 5 seconds
            pollStatus();
            setInterval(pollStatus, 5000);
        }

        // SLA polling — update SLA badges inline every 5s
        const slaResponseSection = document.getElementById('sla-response-section');
        const slaResolutionSection = document.getElementById('sla-resolution-section');

        const slaStatusColors = {
            'BREACHED': { badge: 'bg-red-100 text-red-600', text: 'text-red-600' },
            'CRITICAL': { badge: 'bg-orange-100 text-orange-600', text: 'text-orange-600' },
            'WARNING':  { badge: 'bg-yellow-100 text-yellow-600', text: 'text-yellow-600' },
            'SAFE':     { badge: 'bg-green-100 text-green-600', text: 'text-gray-700' },
            'ON_TRACK': { badge: 'bg-green-100 text-green-600', text: 'text-gray-700' },
        };

        async function pollSla() {
            try {
                const resp = await fetch(`/api/poll/complaint/${complaintId}/status`);
                if (!resp.ok) return;
                const data = await resp.json();

                // Update Response SLA
                if (data.sla_response && slaResponseSection) {
                    const sr = data.sla_response;
                    if (sr.responded) {
                        slaResponseSection.innerHTML = `
                            <p class="text-gray-400 text-xs uppercase mb-1">Response SLA</p>
                            <p class="text-green-600 font-medium">✓ Responded ${escapeHtml(sr.time)}</p>
                            <p class="text-xs text-gray-400">${escapeHtml(sr.diff)} after assignment</p>
                        `;
                    } else {
                        const badge = document.getElementById('sla-response-badge');
                        const deadline = document.getElementById('sla-response-deadline');
                        if (badge) {
                            badge.className = `inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium ${sr.breached ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'}`;
                            badge.textContent = sr.countdown;
                        }
                        if (deadline) {
                            deadline.className = `font-semibold ${sr.breached ? 'text-red-600' : 'text-gray-700'}`;
                        }
                    }
                }

                // Update Resolution SLA
                if (data.sla_resolution && slaResolutionSection) {
                    const sl = data.sla_resolution;
                    if (sl.resolved) {
                        slaResolutionSection.innerHTML = `
                            <p class="text-gray-400 text-xs uppercase mb-1">Resolution SLA</p>
                            <p class="text-green-600 font-medium">✓ Resolved ${escapeHtml(sl.diff)}</p>
                        `;
                    } else {
                        const badge = document.getElementById('sla-resolution-badge');
                        const deadline = document.getElementById('sla-resolution-deadline');
                        const colors = slaStatusColors[sl.sla_status] || slaStatusColors['SAFE'];
                        if (badge) {
                            badge.className = `inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium ${colors.badge}`;
                            badge.textContent = sl.sla_status;

                            // Flash
                            badge.style.transition = 'transform .3s';
                            badge.style.transform = 'scale(1.15)';
                            setTimeout(() => badge.style.transform = 'scale(1)', 400);
                        }
                        if (deadline) {
                            deadline.className = `font-semibold ${colors.text}`;
                        }
                    }
                }
            } catch (e) {}
        }
        pollSla();
        setInterval(pollSla, 5000);
    });

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
