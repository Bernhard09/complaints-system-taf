<div id="sidebar"
     class="w-64 bg-white shadow-md transition-all duration-300 flex flex-col">

    {{-- Header --}}
    <div class="h-16 flex items-center justify-between px-4 border-b">
        <span class="font-bold sidebar-text">Complaint System</span>

        <button id="sidebarToggle" class="text-gray-500 justify-center hover:text-black">
            ☰
        </button>
    </div>

    <nav class="flex-1 p-3 space-y-2 text-sm">

        @if(auth()->user()->role === 'USER')
            <a href="{{ route('user.dashboard') }}"
                class="flex items-center gap-3 p-2 rounded hover:bg-gray-100">
                <span>🏠</span>
                <span class="sidebar-text">Dashboard</span>
            </a>
            
            <a href="{{ route('complaints.create') }}"
                class="flex items-center gap-3 p-2 rounded hover:bg-gray-100">
                <span>➕</span>
                <span class="sidebar-text">New Complaint</span>
            </a>
        @endif

        @if(auth()->user()->role === 'AGENT')
            <a href="{{ route('agent.complaints.index') }}"
                class="flex items-center gap-3 p-2 rounded hover:bg-gray-100">
                <span>📂</span>
                <span class="sidebar-text">My Assigned</span>
            </a>
        @endif

        @if(auth()->user()->role === 'SUPERVISOR')
            <a href="{{ route('supervisor.complaints.index') }}"
                class="flex items-center gap-3 p-2 rounded hover:bg-gray-100">
                <span>📊</span>
                <span class="sidebar-text">Incoming</span>
            </a>
        @endif

    </nav>

    <div class="p-3 border-t">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="flex items-center gap-3 text-red-500 w-full">
                Logout
            </button>
        </form>
    </div>

</div>

<script>
document.getElementById('sidebarToggle')
?.addEventListener('click', function () {

    const sidebar = document.getElementById('sidebar');
    const texts = document.querySelectorAll('.sidebar-text');

    sidebar.classList.toggle('w-64');
    sidebar.classList.toggle('w-16');

    texts.forEach(el => el.classList.toggle('hidden'));
});
</script>

