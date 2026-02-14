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

            {{-- LEFT SIDE: CHAT --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Conversation Card --}}
                <x-ui.card>
                    <h3 class="font-semibold mb-4">Conversation</h3>

                    <div id="chatBox"
                        class="h-[500px] overflow-y-auto space-y-4 pr-2">

                        @if($complaint->status === 'SUBMITTED')
                        <div class="text-gray-500 text-center h-full flex flex-col items-center justify-center">
                            <p class="text-md"> Waiting for agent assignment... </p>
                            <p class="text-xs"> You will be notified once an agent is assigned. </p>
                        </div>
                        @else

                            {{-- form chat --}}

                            @foreach($complaint->messages as $msg)
                                <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">

                                    <div class="max-w-md px-4 py-3 rounded-2xl
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

                    @if($complaint->status !== 'SUBMITTED')
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
                    @endif
                </x-ui.card>

            </div>

            {{-- RIGHT SIDE: INFO PANEL --}}
            <div class="space-y-6">

                <div class="sticky top-6 space-y-6">
                    @if($complaint->sla_resolution_deadline)
                    <x-ui.card>
                        <h3 class="font-semibold mb-4">SLA</h3>

                        @php
                            $deadline = $complaint->sla_resolution_deadline;
                            $isBreached = now()->greaterThan($deadline);
                        @endphp

                        <p class="text-sm">
                            Resolution Deadline:
                        </p>

                        <p class="mt-1 font-semibold {{ $isBreached ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $deadline->format('d M Y H:i') }}
                        </p>

                        @if(!$isBreached)
                            <p class="text-xs text-gray-500 mt-2">
                                {{ $deadline->diffForHumans() }}
                            </p>
                        @else
                            <p class="text-xs text-red-500 mt-2">
                                SLA Breached
                            </p>
                        @endif
                    </x-ui.card>
                    @endif

                    {{-- Complaint Info --}}
                    <x-ui.card>
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
                                <p class="text-gray-600">
                                    {{ $complaint->description }}
                                </p>
                            </div>

                        </div>
                    </x-ui.card>

                    {{-- Attachments Preview --}}
                    {{-- @if($complaint->attachments->count())
                    <x-ui.card class="mt-6">
                        <h3 class="text-sm font-semibold mb-3">Attachments</h3>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                            @foreach($complaint->attachments as $file)
                                <div class="border rounded-lg p-3 text-sm bg-gray-50">

                                    @if(Str::startsWith($file->mime_type, 'image'))
                                        <img
                                            src="{{ asset('storage/'.$file->file_path) }}"
                                            class="w-full h-32 object-cover rounded mb-2"
                                        >
                                    @endif

                                    <p class="truncate">{{ $file->original_name }}</p>

                                    <a
                                        href="{{ route('attachments.download', $file) }}"
                                        class="text-indigo-600 text-xs mt-2 inline-block"
                                    >
                                        Download
                                    </a>
                                </div>
                            @endforeach

                        </div>
                    </x-ui.card>
                    @endif --}}


                    {{-- Agent Info --}}
                    <x-ui.card>
                        <h3 class="font-semibold mb-4">Assigned Agent</h3>

                        @if($complaint->status === 'SUBMITTED')
                            <p class="text-amber-600 text-sm">
                                Waiting for agent assignment
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

                                <div>
                                    <p class="text-gray-400 text-xs uppercase">Agent ID</p>
                                    <p>
                                        {{ $complaint->agent->id ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </x-ui.card>

                    {{-- Activity  --}}
                    <x-ui.card>
                        <h3 class="font-semibold mb-4">Activity</h3>

                        <div class="space-y-4 text-sm">

                            <div>
                                <p class="text-gray-500">
                                    Complaint submitted
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $complaint->created_at->diffForHumans() }}
                                </p>
                            </div>

                            @if($complaint->assigned_at)
                            <div>
                                <p class="text-gray-500">
                                    Assigned to agent
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $complaint->assigned_at->diffForHumans() }}
                                </p>
                            </div>
                            @endif

                            @if($complaint->first_response_at)
                            <div>
                                <p class="text-gray-500">
                                    Agent responded
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $complaint->first_response_at->diffForHumans() }}
                                </p>
                            </div>
                            @endif

                            @if($complaint->confirmed_at)
                            <div>
                                <p class="text-gray-500">
                                    Resolved
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $complaint->confirmed_at->diffForHumans() }}
                                </p>
                            </div>
                            @endif

                        </div>
                    </x-ui.card>

                    {{-- Attachments --}}
                    @if($complaint->attachments->count())
                    <x-ui.card>
                        <h3 class="font-semibold mb-4">Attachments</h3>

                        <div class="space-y-3">

                            @foreach($complaint->attachments as $file)

                                @php
                                    $isImage = str_starts_with($file->mime_type, 'image/');
                                @endphp

                                <div class="flex items-center justify-between border rounded-lg p-3">

                                    <div class="flex items-center gap-3">

                                        @if($isImage)
                                            <img src="{{ asset('storage/' . $file->file_path) }}"
                                                class="w-12 h-12 object-cover rounded-lg">
                                        @else
                                            <div class="w-12 h-12 bg-gray-100 flex items-center justify-center rounded-lg">
                                                📄
                                            </div>
                                        @endif

                                        <div>
                                            <p class="text-sm font-medium">
                                                {{ $file->file_name }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{ number_format($file->file_size / 1024, 1) }} KB
                                            </p>
                                        </div>

                                    </div>

                                    <a href="{{ asset('storage/' . $file->file_path) }}"
                                    target="_blank"
                                    class="text-indigo-600 text-sm hover:underline">
                                        View
                                    </a>

                                </div>

                            @endforeach

                        </div>
                    </x-ui.card>
                    @endif

                </div>
            </div>

        </div>
    </div>
    {{-- <div class="max-w-6xl mx-auto space-y-6">


        <x-ui.card>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="text-xs text-gray-400 uppercase">Contract</p>
                <p class="font-semibold">{{ $complaint->contract_number }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 uppercase">Ticket ID</p>
                <p class="font-semibold">#{{ $complaint->id }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 uppercase">Status</p>
                <x-ui.status-badge :status="$complaint->status"/>
            </div>

            <div>
                <p class="text-xs text-gray-400 uppercase">Reason</p>
                <p class="text-gray-700">{{ $complaint->complaint_reason }}</p>
            </div>

            <div class="col-span-2">
                <p class="text-xs text-gray-400 uppercase">Description</p>
                <p class="text-gray-700">{{ $complaint->description }}</p>
            </div>
        </div>
    </x-ui.card>

    @if ($user->role == 'USER')
    <x-ui.card>
        @if($complaint->status === 'SUBMITTED')
            <p class="text-amber-600 font-medium">
                Waiting for agent assignment
            </p>
            <p class="text-sm text-gray-500 mt-1">
                Our team will review your complaint shortly.
            </p>
        @else
            <div>
                <p class="text-xs text-gray-400 uppercase">Assigned Agent</p>
                <p class="font-semibold">
                    {{ $complaint->agent->name ?? '-' }}
                </p>
                <p class="text-sm text-gray-500">
                    {{ $complaint->agent->department->name ?? '' }}
                </p>
            </div>
        @endif
    </x-ui.card>
    @endif





    @if(in_array($user->role, ['USER', 'AGENT', 'SUPERVISOR']))
    <h3 class="font-semibold">Conversation</h3>
    <div class="bg-white border rounded p-4 h-96 overflow-y-auto space-y-4
        {{ $complaint->status === 'SUBMITTED' ? 'flex flex-col justify-between ' : '' }}"
        id="chatBox">
        @if($complaint->status === 'SUBMITTED')

            <div class="text-gray-500 text-center h-full flex flex-col items-center justify-center">
                <p class="text-md"> Waiting for agent assignment... </p>
                <p class="text-xs"> You will be notified once an agent is assigned. </p>
            </div>
            {{-- <div class="flex items-center">
                <div class="flex-1 border rounded p-2  text-gray-300">Type your message...</div>
                <div class="bg-blue-400 ml-2 text-gray-100 px-4 py-2 rounded cursor-pointerw">
                    Send
                </div>
            </div> --}}

        {{-- @else
            {{-- form chat --}}
            {{-- @foreach($complaint->messages as $msg)
                <div class="flex {{ $msg->sender_id === $user->id ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xs p-3 rounded
                        {{ $msg->sender_role === 'USER'
                            ? 'bg-gray-100'
                            : 'bg-blue-100 text-blue-900' }}">
                        <p class="text-sm">{{ $msg->message }}</p>
                        <span class="text-xs text-gray-500">
                            {{ $msg->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @endforeach
            @if($user->role === 'SUPERVISOR')
                <p class="text-sm text-gray-500 italic">
                    Supervisor view (read-only)
                </p>
            @endif
        </div>
        @endif
        @endif

        @if($user->role === 'USER')
        <form method="POST"
            action="{{ route('complaints.messages.user', $complaint) }}"
            class="flex gap-2">
            @csrf
            <input name="message"
                class="flex-1 border rounded p-2 {{ $complaint->status === 'SUBMITTED' ? 'opacity-50 cursor-not-allowed border-opacity-12' : '' }}"
                placeholder="Type your message..."
                {{ $complaint->status === 'SUBMITTED' ? 'disabled' : 'required' }}>
            <button class=" text-white px-4 rounded {{ $complaint->status === 'SUBMITTED' ? 'opacity-50 cursor-not-allowed bg-blue-400' : 'bg-blue-600 hover:bg-blue-700' }}"
                {{ $complaint->status === 'SUBMITTED' ? 'disabled'
                : '' }}>
                Send
            </button>
        </form>
        @endif --}}



    @if($user->role === 'USER' && $complaint->status === 'WAITING_CONFIRMATION')
    <form method="POST"
        action="{{ route('complaints.confirm', $complaint) }}"
        class="mt-4">
        @csrf
        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Confirm Issue Resolved
        </button>
    </form>

    <p class="text-sm text-gray-500 mt-2">
        If the issue is not resolved, please reply in the conversation.
    </p>
    @endif


    @if($user->role === 'AGENT')
    <form method="POST"
        action="{{ route('agent.complaints.messages', $complaint) }}"
        class="flex gap-2">
        @csrf
        <input name="message"
            class="flex-1 border rounded p-2"
            placeholder="Reply to user..."
            required>
        <button class="bg-blue-600 text-white px-4 rounded">
            Send
        </button>
    </form>

    {{-- Waiting user response button --}}
    <form method="POST"
        action="{{ route('agent.complaints.waiting', $complaint) }}">
        @csrf
        <button
            @disabled($status !== 'IN_PROGRESS')
            class="px-4 py-2 rounded text-white
                {{ $status === 'IN_PROGRESS'
                        ? 'bg-yellow-500 hover:bg-yellow-600'
                        : 'bg-yellow-900 opacity-50 cursor-not-allowed' }}">
            Waiting User Response
        </button>
    </form>

    {{-- Request confirmation button --}}
    <form method="POST"
        action="{{ route('agent.complaints.requestConfirmation', $complaint) }}">
        @csrf
        <button
            @disabled($status !== 'IN_PROGRESS')
            class="px-4 py-2 rounded text-white
                {{ $status === 'IN_PROGRESS'
                        ? 'bg-blue-600 hover:bg-blue-700'
                        : 'bg-blue-900 opacity-50 cursor-not-allowed' }}">
            Request Confirmation
        </button>
    </form>

    {{-- Close complaint button --}}
    <form method="POST"
        action="{{ route('agent.complaints.close', $complaint) }}">
        @csrf
        <button
            @disabled($status !== 'WAITING_CONFIRMATION')
            class="px-4 py-2 rounded text-white
                {{ $status === 'WAITING_CONFIRMATION'
                        ? 'bg-green-600 hover:bg-green-700'
                        : 'bg-green-900 opacity-50 cursor-not-allowed' }}">
            Close Complaint
        </button>
    </form>


    @endif

    {{-- @if($user->role === 'AGENT' && $complaint->status !== 'RESOLVED')
    <div class="flex gap-2 mt-3">
        <form method="POST"
            action="{{ route('agent.complaints.waiting', $complaint) }}">
            @csrf
            <button class="bg-yellow-500 text-white px-3 py-1 rounded">
                Waiting User
            </button>
        </form>

        <form method="POST"
            action="{{ route('agent.complaints.resolve', $complaint) }}">
            @csrf
            <button class="bg-green-600 text-white px-3 py-1 rounded">
                Resolve
            </button>
        </form>
    </div>
    @endif --}}


    @if(in_array($user->role, ['AGENT', 'SUPERVISOR']))
    <div class="bg-yellow-50 border rounded p-4 space-y-3">
        <h3 class="font-semibold">Internal Notes</h3>

        @foreach($complaint->internalNotes as $note)
            <div class="border-b pb-2">
                <p class="text-sm">{{ $note->note }}</p>
                <span class="text-xs text-gray-500">
                    {{ $note->author->name }} ({{ $note->author_role }})
                </span>
            </div>
        @endforeach

        <form method="POST"
            action="{{ route('complaints.internal-notes.store', $complaint) }}"
            class="flex gap-2 mt-2">
            @csrf
            <input name="note"
                class="flex-1 border rounded p-2"
                placeholder="Add internal note..."
                required>
            <button class="bg-yellow-600 text-white px-4 rounded">
                Add
            </button>
        </form>
    </div>
    @endif
</x-app-layout>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const chatBox = document.getElementById("chatBox");
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    });
</script>
