<div class="p-6 space-y-6">

    <div class="text-xl font-bold">
        Complaint System
    </div>

    <nav class="space-y-2">
        @if(auth()->user()->role === 'USER')
            <a href="{{ route('user.dashboard') }}"
                class="block px-3 py-2 rounded-lg hover:bg-gray-100">
                Dashboard
            </a>

            <a href="{{ route('complaints.create') }}"
                class="block px-3 py-2 rounded-lg hover:bg-gray-100">
                New Complaint
            </a>
        @endif

        @if(auth()->user()->role === 'AGENT')
            <a href="{{ route('agent.complaints.index') }}"
                class="block px-3 py-2 rounded-lg hover:bg-gray-100">
                My Assigned
            </a>
        @endif

        @if(auth()->user()->role === 'SUPERVISOR')
            <a href="{{ route('supervisor.complaints.index') }}"
                class="block px-3 py-2 rounded-lg hover:bg-gray-100">
                Incoming
            </a>
        @endif
    </nav>

    <div class="pt-6 border-t">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="block px-3 py-2 rounded-lg text-red-500 w-full hover:bg-gray-100">
                Logout
            </button>
        </form>
    </div>

</div>


