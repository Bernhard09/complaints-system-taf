<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">
            Dashboard
        </h2>
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-6 sm:py-10">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

    {{-- ================= METRICS ================= --}}
    <div class="flex flex-wrap gap-4">

        <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-orange-500 flex-1 min-w-[160px]">
            <p class="text-xs text-gray-500">{{ __('UNASSIGNED') }}</p>
            <p class="text-2xl font-semibold text-orange-500" data-poll-key="incoming">
                {{ $metrics['incoming'] ?? 0 }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-indigo-500 flex-1 min-w-[160px]">
            <p class="text-xs text-gray-500">{{ __('ASSIGNED') }}</p>
            <p class="text-2xl font-semibold text-indigo-500" data-poll-key="assigned">
                {{ $metrics['assigned'] ?? 0 }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-blue-500 flex-1 min-w-[160px]">
            <p class="text-xs text-gray-500">{{ __('IN PROGRESS') }}</p>
            <p class="text-2xl font-semibold text-blue-500" data-poll-key="in_progress">
                {{ $metrics['in_progress'] ?? 0 }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-red-500 flex-1 min-w-[160px]">
            <p class="text-xs text-gray-500">{{ __('BREACHED SLA') }}</p>
            <p class="text-2xl font-semibold text-red-500" data-poll-key="breached">
                {{ $metrics['breached'] ?? 0 }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-green-500 flex-1 min-w-[160px]">
            <p class="text-xs text-gray-500">{{ __('RESOLVED TODAY') }}</p>
            <p class="text-2xl font-semibold text-green-500" data-poll-key="resolved_today">
                {{ $metrics['resolved_today'] ?? 0 }}
            </p>
        </div>

    </div>

    {{-- ================= SLA BLOCK ================= --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="font-semibold mb-2">{{ __('SLA Monitoring') }}</h3>
        @if(($metrics['breached'] ?? 0) == 0)
            <p class="text-gray-500 text-sm">No SLA issues detected.</p>
        @else
            <p class="text-red-500 text-sm">
                {{ $metrics['breached'] }} {{ __('complaints breached SLA.') }}
            </p>
        @endif
    </div>

    {{-- NOTIFICATIONS --}}
    @include('partials.notification-card')

    {{-- ================= MAIN WORKSPACE ================= --}}
    @php
        $supervisorStatuses = ['SUBMITTED', 'ASSIGNED', 'IN_PROGRESS', 'WAITING_USER', 'WAITING_CONFIRMATION', 'RESOLVED'];
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden">

        <div class="h-1 bg-indigo-500"></div>

        <div class="p-6 space-y-6">

            {{-- ================= TOGGLE ROW ================= --}}
            <div class="flex justify-between items-center">

                <div class="flex gap-3">

                    <a href="{{ request()->fullUrlWithQuery(['view'=>'kanban']) }}"
                        class="px-4 py-2 rounded-lg text-sm
                        {{ request('view','kanban')==='kanban'
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Kanban
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['view'=>'table']) }}"
                        class="px-4 py-2 rounded-lg text-sm
                        {{ request('view')==='table'
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Table
                    </a>

                </div>

            </div>

            {{-- ================= FILTER PANEL (auto-visible in table mode) ================= --}}
            @if(request('view') === 'table')
                <div class="bg-gray-50 border rounded-xl p-4">

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        {{-- Keep existing query --}}
                        @foreach(request()->except(['from','to','search']) as $key=>$value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach

                        {{-- FROM DATE --}}
                        <div>
                            <label class="text-xs text-gray-500">From</label>
                            <input type="date"
                                name="from"
                                value="{{ request('from') }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 text-sm
                                        focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- TO DATE --}}
                        <div>
                            <label class="text-xs text-gray-500">To</label>
                            <input type="date"
                                name="to"
                                value="{{ request('to') }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 text-sm
                                        focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- SEARCH --}}
                        <div>
                            <label class="text-xs text-gray-500">Search</label>
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Contract, reason, or user..."
                                class="w-full mt-1 border rounded-lg px-3 py-2 text-sm
                                        focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- BUTTONS --}}
                        <div class="flex items-end gap-2">

                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm">
                                Apply
                            </button>

                            <a href="{{ url()->current() }}?view=table"
                            class="px-4 py-2 rounded-lg bg-gray-200 text-sm">
                                Reset
                            </a>

                        </div>

                    </form>
                </div>
            @endif


            {{-- ================= KANBAN MODE ================= --}}
            @if(request('view','kanban')==='kanban')

                <div class="flex gap-6 overflow-x-auto">

                    @foreach($columns as $status=>$label)

                        <div class="min-w-[20rem] flex-shrink-0 bg-gray-50 rounded-xl p-4 border">

                            <div class="flex justify-between mb-4">
                                <h3 class="text-sm font-semibold text-gray-700">
                                    {{ $label }}
                                </h3>
                                <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">
                                    {{ $board[$status]->count() ?? 0 }}
                                </span>
                            </div>

                            <div class="space-y-4">

                                @forelse($board[$status] ?? [] as $complaint)

                                    <div class="bg-white rounded-lg p-4 shadow-sm border hover:shadow-md transition" data-complaint-id="{{ $complaint->id }}" data-complaint-status="{{ $complaint->status }}">

                                        <div class="text-xs text-gray-400 flex justify-between mb-1">
                                            <span>#{{ $complaint->id }}</span>
                                            
                                            @if($complaint->sla_status === 'BREACHED' && !in_array($complaint->status, ['RESOLVED', 'CLOSED']))
                                                <span class="text-red-600 font-semibold bg-red-100 px-1.5 py-0.5 rounded text-[10px]">
                                                    BREACHED
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-xs font-semibold text-gray-600">
                                            {{ $complaint->contract_number ?? '-' }}
                                        </div>

                                        <p class="mt-2 font-medium text-sm text-gray-900">
                                            {{ $complaint->complaint_reason }}
                                        </p>

                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $complaint->user->name }}
                                        </p>

                                        <div class="flex justify-between items-center mt-3 text-xs">
                                            <span class="text-gray-400">
                                                {{ $complaint->created_at->diffForHumans() }}
                                            </span>

                                            <a href="{{ route('supervisor.complaints.show', $complaint) }}"
                                                class="text-indigo-600 hover:underline">
                                                View →
                                            </a>
                                        </div>

                                    </div>

                                @empty
                                    <p class="text-xs text-gray-400">No complaints</p>
                                @endforelse

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif



            {{-- ================= TABLE MODE ================= --}}
            @if(request('view')==='table')

                @php
                    $allStatuses = ['ALL','SUBMITTED','ASSIGNED','IN_PROGRESS','WAITING_USER','WAITING_CONFIRMATION','RESOLVED'];
                    $current = request('status','ALL');
                @endphp

                {{-- STATUS TABS --}}
                <div class="flex gap-6 text-sm border-b pb-3">
                    @foreach($allStatuses as $status)
                        <a href="{{ request()->fullUrlWithQuery([
                                'status'=>$status==='ALL'?null:$status,
                                'view'=>'table'
                            ]) }}"
                           class="pb-1 transition
                           {{ $current === $status
                                ? 'border-b-2 border-indigo-600 text-indigo-600'
                                : 'text-gray-500 hover:text-gray-700' }}">
                            {{ __(str_replace('_', ' ', $status)) }}
                        </a>
                    @endforeach
                </div>


                {{-- SHOW ENTRIES --}}
                <div class="flex justify-end text-sm">
                    <form method="GET" class="flex items-center gap-2">

                        <span class="text-gray-500">Show</span>

                        <select name="per_page"
                                onchange="this.form.submit()"
                                class="border border-gray-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-indigo-500">

                            @foreach([10,25,50,100] as $size)
                                <option value="{{ $size }}"
                                    {{ request('per_page',50)==$size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach

                        </select>

                        <span class="text-gray-500">entries</span>

                        @foreach(request()->except('per_page') as $key=>$value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach

                    </form>
                </div>



                {{-- TABLE --}}
                <div class="overflow-x-auto rounded-xl border">
                    <table class="w-full text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left">ID</th>
                                <th class="px-6 py-3 text-left">Contract</th>
                                <th class="px-6 py-3 text-left">Reason</th>
                                <th class="px-6 py-3 text-left">User</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Agent</th>
                                <th class="px-6 py-3 text-left">Created</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach($allTickets as $ticket)

                                @php
                                    $color = match($ticket->status) {
                                        'SUBMITTED'=>'bg-orange-100 text-orange-700',
                                        'ASSIGNED'=>'bg-indigo-100 text-indigo-700',
                                        'IN_PROGRESS'=>'bg-blue-100 text-blue-700',
                                        'WAITING_USER'=>'bg-amber-100 text-amber-700',
                                        'WAITING_CONFIRMATION'=>'bg-purple-100 text-purple-700',
                                        'RESOLVED'=>'bg-green-100 text-green-700',
                                        default=>'bg-gray-100 text-gray-600'
                                    };
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">#{{ $ticket->id }}</td>
                                    <td class="px-6 py-4">{{ $ticket->contract_number }}</td>
                                    <td class="px-6 py-4">{{ $ticket->complaint_reason }}</td>
                                    <td class="px-6 py-4">{{ $ticket->user->name }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $color }}" data-complaint-id="{{ $ticket->id }}" data-complaint-status="{{ $ticket->status }}">
                                            {{ str_replace('_',' ', $ticket->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ optional($ticket->agent)->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $ticket->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 text-indigo-600">
                                        <a href="{{ route('supervisor.complaints.show', $ticket) }}">
                                            View →
                                        </a>
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div>
                    {{ $allTickets->links() }}
                </div>

            @endif

        </div>
    </div>

        </div>
    </div>

</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    async function pollSupervisorDashboard() {
        try {
            const resp = await fetch('/api/poll/supervisor-dashboard');
            if (!resp.ok) return;
            const data = await resp.json();

            // Update metric cards
            document.querySelectorAll('[data-poll-key]').forEach(el => {
                const key = el.dataset.pollKey;
                if (data[key] !== undefined && el.textContent.trim() !== String(data[key])) {
                    el.textContent = data[key];
                    el.style.transition = 'color .3s';
                    const origColor = el.style.color;
                    el.style.color = '#4f46e5';
                    setTimeout(() => el.style.color = origColor, 800);
                }
            });

            // Update complaint statuses — reload if any status changed
            if (data.complaints) {
                let needsReload = false;
                data.complaints.forEach(c => {
                    document.querySelectorAll(`[data-complaint-id="${c.id}"]`).forEach(el => {
                        const oldStatus = el.dataset.complaintStatus;
                        if (oldStatus && oldStatus !== c.status) {
                            needsReload = true;
                        }
                    });
                });
                if (needsReload) {
                    location.reload();
                }
            }
        } catch (e) {}
    }
    setInterval(pollSupervisorDashboard, 10000);
});
</script>
