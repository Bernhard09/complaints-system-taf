@php
    $user = auth()->user();
    $status = $complaint->status;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">
            Complaint #{{ $complaint->id }}
        </h2>
    </x-slot>

    <div class="mx-auto w-full max-w-screen-2xl px-10 py-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ===================================== --}}
            {{-- LEFT SIDE : CONVERSATION --}}
            {{-- ===================================== --}}
            <div class="lg:col-span-2 space-y-6">

                <x-ui.card class="p-6">

                    <h3 class="font-semibold mb-4">Conversation</h3>

                    <div id="chatBox"
                            class="h-[65vh] overflow-y-auto space-y-4 pr-2 scroll-smooth">

                        @if($complaint->status === 'SUBMITTED')
                            <div class="text-gray-500 text-center h-full flex flex-col items-center justify-center">
                                <p class="text-md">Waiting for agent assignment...</p>
                                <p class="text-xs">You will be notified once assigned.</p>
                            </div>
                        @else

                            @foreach($complaint->messages as $msg)
                                <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">

                                    <div class="max-w-[70%] px-4 py-3 rounded-2xl
                                        {{ $msg->sender_id === auth()->id()
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-100 text-gray-800' }}">

                                        <p class="text-sm">{{ $msg->message }}</p>

                                        <p class="text-[10px] mt-2 opacity-70">
                                            {{ $msg->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach

                        @endif
                    </div>

                    {{-- Chat Input --}}
                    @if($complaint->status !== 'SUBMITTED'
                        && in_array($user->role, ['USER','AGENT']))
                        <form method="POST"
                                action="{{ route('complaints.messages.user', $complaint) }}"
                                class="mt-4 flex gap-3">
                            @csrf
                            <input name="message"
                                    class="flex-1 border rounded-xl px-4 py-2"
                                    placeholder="Type your message..."
                                    required />
                            <x-ui.button>Send</x-ui.button>
                        </form>
                    @else
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
                    @if($complaint->sla_resolution_deadline)
                        @php
                            $deadline = $complaint->sla_resolution_deadline;
                            $isBreached = now()->greaterThan($deadline);
                        @endphp

                        <x-ui.card class="p-6">
                            <h3 class="font-semibold mb-4">SLA</h3>

                            <p class="text-sm">Resolution Deadline</p>

                            <p class="mt-1 font-semibold
                                {{ $isBreached ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $deadline->format('d M Y H:i') }}
                            </p>

                            <p class="text-xs mt-2
                                {{ $isBreached ? 'text-red-500' : 'text-gray-500' }}">
                                {{ $isBreached ? 'SLA Breached'
                                                : $deadline->diffForHumans() }}
                            </p>
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
                                <x-ui.status-badge :status="$complaint->status" />
                            </div>

                            <div>
                                <p class="text-gray-400 text-xs uppercase">Reason</p>
                                <p>{{ $complaint->complaint_reason }}</p>
                            </div>

                            <div>
                                <p class="text-gray-400 text-xs uppercase">Description</p>
                                <p class="text-gray-600">{{ $complaint->description }}</p>
                            </div>

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
                                    <p class="text-gray-400 text-xs uppercase">Name</p>
                                    <p class="font-semibold">
                                        {{ $complaint->agent->name ?? '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-gray-400 text-xs uppercase">Department</p>
                                    <p>
                                        {{ $complaint->agent->department->name ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </x-ui.card>

                    {{-- Activity --}}
                    <x-ui.card class="p-6">
                        <h3 class="font-semibold mb-4">Activity</h3>

                        <div class="space-y-3 text-sm">

                            <div>
                                Complaint submitted
                                <p class="text-xs text-gray-400">
                                    {{ $complaint->created_at->diffForHumans() }}
                                </p>
                            </div>

                            @if($complaint->assigned_at)
                                <div>
                                    Assigned to agent
                                    <p class="text-xs text-gray-400">
                                        {{ $complaint->assigned_at->diffForHumans() }}
                                    </p>
                                </div>
                            @endif

                        </div>
                    </x-ui.card>

                    {{-- Actions --}}
                    <x-ui.card class="p-6">
                        <h3 class="font-semibold mb-4">Actions</h3>

                        <div class="space-y-3">

                            @if($user->role === 'USER'
                                && $status === 'WAITING_CONFIRMATION')

                                <form method="POST"
                                        action="{{ route('complaints.confirm', $complaint) }}">
                                    @csrf
                                    <button class="w-full bg-green-600 text-white py-2 rounded-lg">
                                        Confirm Resolved
                                    </button>
                                </form>
                            @endif

                            @if($user->role === 'AGENT')

                                <form method="POST"
                                        action="{{ route('agent.complaints.waiting', $complaint) }}">
                                    @csrf
                                    <button class="w-full bg-yellow-500 text-white py-2 rounded-lg">
                                        Waiting User
                                    </button>
                                </form>

                                <form method="POST"
                                        action="{{ route('agent.complaints.requestConfirmation', $complaint) }}">
                                    @csrf
                                    <button class="w-full bg-blue-600 text-white py-2 rounded-lg">
                                        Request Confirmation
                                    </button>
                                </form>

                                <form method="POST"
                                        action="{{ route('agent.complaints.close', $complaint) }}">
                                    @csrf
                                    <button class="w-full bg-green-600 text-white py-2 rounded-lg">
                                        Close Complaint
                                    </button>
                                </form>
                            @endif

                        </div>
                    </x-ui.card>

                    {{-- Internal Notes --}}
                    @if(in_array($user->role, ['AGENT','SUPERVISOR']))
                        <x-ui.card class="p-6">
                            <h3 class="font-semibold mb-4">Internal Notes</h3>

                            <div class="space-y-3 max-h-60 overflow-y-auto text-sm">

                                @foreach($complaint->internalNotes as $note)
                                    <div class="border-b pb-2">
                                        <p>{{ $note->note }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $note->author->name }}
                                            • {{ $note->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                @endforeach

                            </div>

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
    });
</script>
