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

    <div class="max-w-4xl mx-auto space-y-6">

    @if(in_array($user->role, ['USER', 'AGENT', 'SUPERVISOR']))
    <div class="bg-white border rounded p-4 space-y-3">
        <h3 class="font-semibold">Conversation</h3>

        @foreach($complaint->messages as $msg)
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

    @if($user->role === 'USER')
    <form method="POST"
        action="{{ route('complaints.messages.user', $complaint) }}"
        class="flex gap-2">
        @csrf
        <input name="message"
            class="flex-1 border rounded p-2"
            placeholder="Type your message..."
            required>
        <button class="bg-blue-600 text-white px-4 rounded">
            Send
        </button>
    </form>

    @endif

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
