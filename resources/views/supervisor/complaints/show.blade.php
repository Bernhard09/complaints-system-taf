<x-app-layout>

<div
    x-data="{
        openReassign: false,
        department: '',
        agent: '',
        departments: {{ $departments->toJson() }},
        agents: []
    }"
>

    <x-slot name="header">
        Complaint #{{ $complaint->id }}
    </x-slot>

    <div class="w-full py-6 sm:py-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ================= LEFT SIDE ================= --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Complaint Info --}}
                <x-ui.card class="p-6">
                    <h2 class="text-lg font-semibold mb-4">
                        Complaint Information
                    </h2>

                    <div class="space-y-3 text-sm">

                        <div>
                            <span class="text-gray-500">Contract:</span>
                            <span class="font-medium">
                                {{ $complaint->contract_number }}
                            </span>
                        </div>

                        <div>
                            <span class="text-gray-500">Reason:</span>
                            <span class="font-medium">
                                {{ $complaint->complaint_reason }}
                            </span>
                        </div>

                        <div>
                            <span class="text-gray-500">Description:</span>
                            <p class="mt-1 text-gray-700">
                                {{ $complaint->description }}
                            </p>
                        </div>

                        <div>
                            <span class="text-gray-500">Submitted by:</span>
                            <span class="font-medium">
                                {{ $complaint->user->name }}
                            </span>
                        </div>

                    </div>
                </x-ui.card>


                {{-- Attachments --}}
                @if($complaint->attachments->count())
                <x-ui.card class="p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        Attachments
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                        @foreach($complaint->attachments as $file)

                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="block">
                                {{-- Thumbnail --}}
                                @if(Str::contains($file->mime_type, 'image'))
                                    <img
                                        src="{{ asset('storage/' . $file->file_path) }}"
                                        class="rounded-lg cursor-pointer border hover:opacity-90 transition"
                                    >
                                @else
                                    <div
                                        class="p-6 bg-gray-100 rounded-lg text-center hover:bg-gray-200 transition"
                                    >
                                        📄 PDF File
                                    </div>
                                @endif
                            </a>

                        @endforeach

                    </div>
                </x-ui.card>
                @endif

            </div>


            {{-- ================= RIGHT SIDE ================= --}}
            <div class="space-y-6">

                {{-- Assignment --}}
                <x-ui.card class="p-6">

                    <h3 class="text-lg font-semibold mb-4">
                        Assignment
                    </h3>

                    @if(!$complaint->agent_id && $complaint->status === 'SUBMITTED')

                        <form method="POST"
                              action="{{ route('supervisor.complaints.assign', $complaint) }}">
                            @csrf

                            {{-- Department --}}
                            <select
                                x-model="department"
                                @change="
                                    let dept = departments.find(d => d.id == department);
                                    agents = dept ? dept.agents : [];
                                    agent = '';
                                "
                                name="department_id"
                                class="w-full mb-3 rounded-lg border"
                            >
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Agent --}}
                            <select
                                name="agent_id"
                                x-model="agent"
                                class="w-full rounded-lg border"
                                required 
                            >
                                <option value="">Select Agent</option>
                                <template x-for="a in agents" :key="a.id">
                                    <option :value="a.id" x-text="a.name"></option>
                                </template>
                            </select>

                            {{-- Resolution SLA Deadline --}}
                            <div class="mt-3">
                                <label class="text-xs text-gray-500">Resolution Deadline (optional)</label>
                                <input type="datetime-local"
                                       name="sla_resolution_deadline"
                                       class="w-full mt-1 rounded-lg border text-sm px-3 py-2
                                              focus:ring-2 focus:ring-indigo-500">
                                <p class="text-xs text-gray-400 mt-1">Default: 3 days from now</p>
                            </div>

                            <button
                                class="mt-4 w-full bg-indigo-600 text-white py-2 rounded-lg"
                            >
                                Assign Agent
                            </button>

                        </form>

                    @else
                        <div class="text-sm text-gray-600">
                            Assigned to:
                            <span class="font-medium">
                                {{ $complaint->agent?->name }}
                            </span>
                        </div>
                    @endif

                </x-ui.card>


                {{-- Actions --}}
                <x-ui.card class="p-6">

                    <h3 class="text-sm font-semibold mb-4">
                        Actions
                    </h3>

                    <div class="space-y-3">

                        {{-- Reassign --}}
                        @if(in_array($complaint->status, ['IN_PROGRESS', 'ASSIGNED']))
                            <button
                                @click="openReassign = true"
                                class="text-sm text-indigo-600 hover:underline"
                            >
                                Reassign
                            </button>
                        @else
                            <button disabled class="text-sm text-indigo-600/50 cursor-not-allowed">
                                Reassign
                            </button>
                        @endif

                        {{-- Pending Reassign Info --}}
                        @if($complaint->status === 'PENDING_REASSIGN')
                            @php $pending = $complaint->pendingReassignment(); @endphp
                            @if($pending)
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                    <p class="text-xs font-semibold text-amber-700">⏳ Pending Reassign</p>
                                    <p class="text-xs text-amber-600 mt-1">To: {{ $pending->toAgent?->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-amber-600">Dept: {{ $pending->toDepartment?->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Waiting for agent confirmation</p>
                                </div>
                            @endif
                        @endif

                        {{-- Reopen --}}
                        @if($complaint->status === 'RESOLVED')
                            <form method="POST"
                                  action="{{ route('supervisor.complaints.reopen', $complaint) }}">
                                @csrf
                                <button
                                    class="text-sm text-red-600 hover:underline"
                                >
                                    Reopen Complaint
                                </button>
                            </form>
                        @else
                            <button disabled class="text-sm text-red-600/50 cursor-not-allowed">
                                Reopen Complaint
                            </button>
                        @endif

                        {{-- Chat Link --}}
                        <div>
                            @if($complaint->agent)
                                <a href="{{ route('complaints.show', $complaint) }}"
                                   class="text-indigo-600 text-sm hover:underline">
                                    View Conversation →
                                </a>
                            @else
                                <span class="text-gray-300 text-sm cursor-not-allowed">
                                    View Conversation →
                                </span>
                            @endif
                        </div>

                    </div>

                </x-ui.card>


                {{-- Activity Log --}}
                <x-ui.card class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Activity</h3>

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
                                        <p>Assigned to <span class="font-medium">{{ $complaint->agent?->name }}</span></p>
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


                {{-- SLA Information --}}
                <x-ui.card class="p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        SLA Information
                    </h3>

                    @if($complaint->status === 'SUBMITTED')
                        <p class="text-sm text-gray-400">SLA will start after assignment.</p>
                    @else
                        <div class="space-y-4 text-sm">

                            {{-- Response SLA --}}
                            <div id="sla-response-section">
                                <p class="text-gray-400 text-xs uppercase">Response SLA</p>
                                @if($complaint->first_response_at)
                                    <p class="text-green-600 font-medium" id="sla-response-text">
                                        ✓ Responded {{ $complaint->first_response_at->diffForHumans() }}
                                    </p>
                                @elseif($complaint->sla_response_deadline)
                                    @php $respBreached = now()->greaterThan($complaint->sla_response_deadline); @endphp
                                    <p class="font-semibold {{ $respBreached ? 'text-red-600' : 'text-gray-700' }}" id="sla-response-deadline">
                                        {{ $complaint->sla_response_deadline->format('d M Y H:i') }}
                                    </p>
                                    <p class="text-xs {{ $respBreached ? 'text-red-500' : 'text-gray-500' }}" id="sla-response-badge">
                                        {{ $respBreached ? 'BREACHED' : $complaint->sla_response_deadline->diffForHumans() }}
                                    </p>
                                @else
                                    <p class="text-gray-400">Not set</p>
                                @endif
                            </div>

                            {{-- Resolution SLA --}}
                            <div id="sla-resolution-section">
                                <p class="text-gray-400 text-xs uppercase">Resolution SLA</p>
                                @if(in_array($complaint->status, ['RESOLVED', 'CLOSED']))
                                    <p class="text-green-600 font-medium" id="sla-resolution-text">
                                        ✓ Resolved {{ $complaint->resolved_at ? $complaint->resolved_at->diffForHumans() : '' }}
                                    </p>
                                @elseif($complaint->sla_resolution_deadline)
                                    @php
                                        $resDeadline = $complaint->sla_resolution_deadline;
                                        $slaStatus = $complaint->sla_status;
                                        $slaColor = match($slaStatus) {
                                            'BREACHED' => 'text-red-600',
                                            'CRITICAL' => 'text-orange-600',
                                            'WARNING'  => 'text-yellow-600',
                                            default    => 'text-gray-700',
                                        };
                                    @endphp
                                    <p class="font-semibold {{ $slaColor }}" id="sla-resolution-deadline">
                                        {{ $resDeadline->format('d M Y H:i') }}
                                    </p>
                                    <span id="sla-resolution-badge" class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ match($slaStatus) {
                                            'BREACHED' => 'bg-red-100 text-red-600',
                                            'CRITICAL' => 'bg-orange-100 text-orange-600',
                                            'WARNING'  => 'bg-yellow-100 text-yellow-600',
                                            default    => 'bg-green-100 text-green-600',
                                        } }}">
                                        {{ $slaStatus }}
                                    </span>
                                @else
                                    <p class="text-gray-400">Not set</p>
                                @endif
                            </div>

                        </div>
                    @endif
                </x-ui.card>

            </div>

        </div>
    </div>


    {{-- ================= REASSIGN MODAL ================= --}}
    <div
        x-cloak
        x-show="openReassign"
        x-transition
        class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
    >
        <div
            @click.away="openReassign = false"
            class="bg-white w-full max-w-lg rounded-2xl p-6 shadow-xl"
        >

            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold">Reassign Complaint</h3>
                <button @click="openReassign = false">✕</button>
            </div>

            <form method="POST"
                  action="{{ route('supervisor.complaints.reassign', $complaint) }}">
                @csrf

                {{-- Department --}}
                <div class="mb-4">
                    <label class="text-xs text-gray-500">Department</label>
                    <select
                        x-model="department"
                        @change="
                            let dept = departments.find(d => d.id == department);
                            agents = dept ? dept.agents : [];
                            agent = '';
                        "
                        name="department_id"
                        class="w-full rounded-lg border"
                    >
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Agent --}}
                <div class="mb-4">
                    <label class="text-xs text-gray-500">Agent</label>
                    <select
                        x-model="agent"
                        name="agent_id"
                        class="w-full rounded-lg border"
                    >
                        <option value="">Select Agent</option>
                        <template x-for="a in agents" :key="a.id">
                            <option :value="a.id" x-text="a.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Reason --}}
                <div class="mb-4">
                    <label class="text-xs text-gray-500">Reason for Reassign</label>
                    <textarea
                        name="reason"
                        rows="3"
                        required
                        placeholder="Explain why this complaint needs to be reassigned..."
                        class="w-full rounded-lg border"
                    ></textarea>
                </div>

                {{-- Resolution SLA Deadline --}}
                <div class="mb-4">
                    <label class="text-xs text-gray-500">New Resolution Deadline (optional)</label>
                    <input type="datetime-local"
                           name="sla_resolution_deadline"
                           class="w-full mt-1 rounded-lg border text-sm px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Default: 3 days from confirmation</p>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="openReassign = false"
                        class="text-gray-500"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg"
                    >
                        Confirm Reassign
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const complaintId = {{ $complaint->id }};
    let currentStatus = '{{ $complaint->status }}';

    const slaStatusColors = {
        'BREACHED': { badge: 'bg-red-100 text-red-600', text: 'text-red-600' },
        'CRITICAL': { badge: 'bg-orange-100 text-orange-600', text: 'text-orange-600' },
        'WARNING':  { badge: 'bg-yellow-100 text-yellow-600', text: 'text-yellow-600' },
        'SAFE':     { badge: 'bg-green-100 text-green-600', text: 'text-gray-700' },
        'ON_TRACK': { badge: 'bg-green-100 text-green-600', text: 'text-gray-700' },
    };

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function pollStatus() {
        try {
            const resp = await fetch(`/api/poll/complaint/${complaintId}/status`);
            if (!resp.ok) return;
            const data = await resp.json();

            // Reload on status change for supervisor (action buttons change)
            if (data.status && data.status !== currentStatus) {
                currentStatus = data.status;
                window.location.reload();
                return;
            }

            // Update Response SLA
            const slaResponseSection = document.getElementById('sla-response-section');
            if (data.sla_response && slaResponseSection) {
                const sr = data.sla_response;
                if (sr.responded) {
                    slaResponseSection.innerHTML = `
                        <p class="text-gray-400 text-xs uppercase">Response SLA</p>
                        <p class="text-green-600 font-medium">✓ Responded ${escapeHtml(sr.time)}</p>
                        <p class="text-xs text-gray-400">${escapeHtml(sr.diff)} after assignment</p>
                    `;
                } else {
                    const badge = document.getElementById('sla-response-badge');
                    const deadline = document.getElementById('sla-response-deadline');
                    if (badge) {
                        badge.className = `text-xs ${sr.breached ? 'text-red-500' : 'text-gray-500'}`;
                        badge.textContent = sr.countdown;
                    }
                    if (deadline) {
                        deadline.className = `font-semibold ${sr.breached ? 'text-red-600' : 'text-gray-700'}`;
                    }
                }
            }

            // Update Resolution SLA
            const slaResolutionSection = document.getElementById('sla-resolution-section');
            if (data.sla_resolution && slaResolutionSection) {
                const sl = data.sla_resolution;
                if (sl.resolved) {
                    slaResolutionSection.innerHTML = `
                        <p class="text-gray-400 text-xs uppercase">Resolution SLA</p>
                        <p class="text-green-600 font-medium">✓ Resolved ${escapeHtml(sl.diff)}</p>
                    `;
                } else {
                    const badge = document.getElementById('sla-resolution-badge');
                    const deadline = document.getElementById('sla-resolution-deadline');
                    const colors = slaStatusColors[sl.sla_status] || slaStatusColors['SAFE'];
                    if (badge) {
                        badge.className = `inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium ${colors.badge}`;
                        badge.textContent = sl.sla_status;

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
    pollStatus();
    setInterval(pollStatus, 5000);
});
</script>
