<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">
            Dashboard
        </h2>
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-6 sm:py-10">
        <div class="max-w-screen-2xl mx-auto space-y-6 sm:space-y-8">

            {{-- ================= OVERVIEW ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @php
                    $overviewCards = [
                        ['label'=>__('Active'),'value'=>$metrics['active'],'color'=>'indigo','key'=>'active'],
                        ['label'=>__('Waiting User'),'value'=>$metrics['waiting'],'color'=>'amber','key'=>'waiting'],
                        ['label'=>__('Breached SLA'),'value'=>$metrics['breached'],'color'=>'red','key'=>'breached'],
                        ['label'=>__('Resolved Today'),'value'=>$metrics['resolved_today'],'color'=>'green','key'=>'resolved_today'],
                    ];
                @endphp

                @foreach($overviewCards as $card)
                    <div class="bg-white rounded-2xl shadow-sm border p-6">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">
                            {{ $card['label'] }}
                        </p>
                        <p class="text-3xl font-bold mt-2 text-{{ $card['color'] }}-600" data-poll-key="{{ $card['key'] }}">
                            {{ $card['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>



            {{-- ================= SLA MONITOR ================= --}}
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">
                        {{ __('SLA Monitoring') }}
                    </h3>

                    @if($breached->count())
                        <span class="text-sm text-red-600 font-medium">
                            {{ $breached->count() }} breached
                        </span>
                    @endif
                </div>

                <div class="max-h-40 overflow-y-auto px-6 py-4 text-sm space-y-3">
                    @forelse($breached as $ticket)
                        <div class="flex justify-between">
                            <span>#{{ $ticket->id }} – {{ $ticket->complaint_reason }}</span>
                            <span class="text-red-600 font-semibold">
                                BREACHED
                            </span>
                        </div>
                    @empty
                        <div class="text-gray-500">
                            {{ __('No SLA issues detected.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- NOTIFICATIONS --}}
            @include('partials.notification-card')


            {{-- ================= UNIFIED BOARD CARD ================= --}}
            @php
                $columns = [
                    'ASSIGNED'=>'ASSIGNED',
                    'IN_PROGRESS'=>'IN PROGRESS',
                    'WAITING_USER'=>'WAITING USER',
                    'WAITING_CONFIRMATION'=>'WAITING CONFIRMATION',
                    'RESOLVED'=>'RESOLVED'
                ];
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

                        <a href="{{ route('agent.complaints.export') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm bg-green-600 text-white hover:bg-green-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V3" />
                            </svg>
                            Download Excel
                        </a>

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

                                <div class="w-80 flex-shrink-0 bg-gray-50 rounded-xl p-4 border" data-kanban-column="{{ $status }}">

                                    <div class="flex justify-between mb-4">
                                        <h3 class="text-sm font-semibold text-gray-700">
                                            {{ $label }}
                                        </h3>
                                        <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full" data-kanban-count>
                                            {{ $board[$status]->count() ?? 0 }}
                                        </span>
                                    </div>

                                    <div class="space-y-4" data-kanban-cards>

                                        @foreach($board[$status] ?? [] as $ticket)

                                            <div class="bg-white rounded-lg p-4 shadow-sm border hover:shadow-md transition" data-complaint-id="{{ $ticket->id }}" data-complaint-status="{{ $ticket->status }}">

                                                <div class="text-xs text-gray-400 flex justify-between mb-1">
                                                    <span>#{{ $ticket->id }}</span>

                                                    @if($ticket->sla_status === 'BREACHED' && !in_array($ticket->status, ['RESOLVED', 'CLOSED']))
                                                        <span class="text-red-600 font-semibold bg-red-100 px-1.5 py-0.5 rounded text-[10px]">
                                                            BREACHED
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="text-xs font-semibold text-gray-600">
                                                    {{ $ticket->contract_number ?? '-' }}
                                                </div>

                                                <div class="mt-2 font-medium text-sm text-gray-900">
                                                    {{ $ticket->complaint_reason }}
                                                </div>

                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $ticket->user->name }}
                                                </div>

                                                <div class="flex justify-between items-center mt-3 text-xs">
                                                    <span class="text-gray-400">
                                                        {{ $ticket->created_at->diffForHumans() }}
                                                    </span>

                                                    <a href="{{ route('complaints.show', $ticket) }}" class="text-indigo-600 hover:underline">
                                                        View →
                                                    </a>
                                                </div>

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif



                    {{-- ================= TABLE MODE ================= --}}
                    @if(request('view')==='table')

                        @php
                            $statuses = ['ALL','ASSIGNED','IN_PROGRESS','WAITING_USER','WAITING_CONFIRMATION','RESOLVED'];
                            $current = request('status','ALL');
                        @endphp

                        {{-- STATUS TABS --}}
                        <div class="flex gap-6 text-sm border-b pb-3">
                            @foreach($statuses as $status)
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
                                        <th class="px-6 py-3 text-left">Reason</th>
                                        <th class="px-6 py-3 text-left">User</th>
                                        <th class="px-6 py-3 text-left">Status</th>
                                        <th class="px-6 py-3 text-left">Created</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y">
                                    @foreach($allTickets as $ticket)

                                        @php
                                            $color = match($ticket->status) {
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
                                            <td class="px-6 py-4">{{ $ticket->complaint_reason }}</td>
                                            <td class="px-6 py-4">{{ $ticket->user->name }}</td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $color }}" data-complaint-id="{{ $ticket->id }}" data-complaint-status="{{ $ticket->status }}">
                                                    {{ str_replace('_',' ', $ticket->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $ticket->created_at->diffForHumans() }}
                                            </td>
                                            <td class="px-6 py-4 text-indigo-600">
                                                <a href="{{ route('complaints.show',$ticket) }}">
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
    const statusColors = {
        'SUBMITTED':            { bg: '#f3f4f6', text: '#4b5563', cls: 'bg-gray-100 text-gray-600' },
        'ASSIGNED':             { bg: '#e0e7ff', text: '#4338ca', cls: 'bg-indigo-100 text-indigo-700' },
        'IN_PROGRESS':          { bg: '#dbeafe', text: '#1d4ed8', cls: 'bg-blue-100 text-blue-700' },
        'WAITING_USER':         { bg: '#fef3c7', text: '#b45309', cls: 'bg-amber-100 text-amber-700' },
        'WAITING_CONFIRMATION': { bg: '#f3e8ff', text: '#7c3aed', cls: 'bg-purple-100 text-purple-700' },
        'RESOLVED':             { bg: '#d1fae5', text: '#065f46', cls: 'bg-green-100 text-green-700' },
    };

    const kanbanStatuses = ['ASSIGNED', 'IN_PROGRESS', 'WAITING_USER', 'WAITING_CONFIRMATION', 'RESOLVED'];
    let prevComplaints = {};

    // Build initial state from DOM
    document.querySelectorAll('[data-complaint-id]').forEach(el => {
        prevComplaints[el.dataset.complaintId] = el.dataset.complaintStatus;
    });

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function buildKanbanCard(c) {
        const slaHtml = (c.sla_status === 'BREACHED' && !['RESOLVED','CLOSED'].includes(c.status))
            ? `<span class="text-red-600 font-semibold bg-red-100 px-1.5 py-0.5 rounded text-[10px]">BREACHED</span>`
            : '';

        return `<div class="bg-white rounded-lg p-4 shadow-sm border hover:shadow-md transition" data-complaint-id="${c.id}" data-complaint-status="${c.status}">
            <div class="text-xs text-gray-400 flex justify-between mb-1">
                <span>#${c.id}</span>
                ${slaHtml}
            </div>
            <div class="text-xs font-semibold text-gray-600">${escapeHtml(c.contract_number)}</div>
            <div class="mt-2 font-medium text-sm text-gray-900">${escapeHtml(c.complaint_reason)}</div>
            <div class="text-xs text-gray-500 mt-1">${escapeHtml(c.user_name)}</div>
            <div class="flex justify-between items-center mt-3 text-xs">
                <span class="text-gray-400">${escapeHtml(c.created_at)}</span>
                <a href="${c.url}" class="text-indigo-600 hover:underline">View →</a>
            </div>
        </div>`;
    }

    function rebuildKanban(complaints) {
        const columns = document.querySelectorAll('[data-kanban-column]');
        if (columns.length === 0) return;

        const grouped = {};
        kanbanStatuses.forEach(s => grouped[s] = []);
        complaints.forEach(c => {
            if (grouped[c.status]) grouped[c.status].push(c);
        });

        columns.forEach(col => {
            const status = col.dataset.kanbanColumn;
            if (!grouped[status]) return;

            // Update count
            const countEl = col.querySelector('[data-kanban-count]');
            if (countEl) countEl.textContent = grouped[status].length;

            // Rebuild cards
            const cardsContainer = col.querySelector('[data-kanban-cards]');
            if (cardsContainer) {
                cardsContainer.innerHTML = grouped[status].map(c => buildKanbanCard(c)).join('');
            }
        });
    }

    function updateTableBadges(complaints) {
        const map = {};
        complaints.forEach(c => map[c.id] = c);

        document.querySelectorAll('table [data-complaint-id]').forEach(el => {
            const c = map[el.dataset.complaintId];
            if (!c || el.dataset.complaintStatus === c.status) return;

            const colors = statusColors[c.status] || statusColors['SUBMITTED'];
            el.className = `px-3 py-1 rounded-full text-xs font-medium ${colors.cls}`;
            el.textContent = c.status.replace(/_/g, ' ');
            el.dataset.complaintStatus = c.status;

            // Flash animation
            el.style.transition = 'transform .3s';
            el.style.transform = 'scale(1.15)';
            setTimeout(() => el.style.transform = 'scale(1)', 400);
        });
    }

    async function pollAgentDashboard() {
        try {
            const resp = await fetch('/api/poll/agent-dashboard');
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

            // Update kanban and table
            if (data.complaints) {
                rebuildKanban(data.complaints);
                updateTableBadges(data.complaints);

                // Update previous state
                prevComplaints = {};
                data.complaints.forEach(c => prevComplaints[c.id] = c.status);
            }
        } catch (e) {}
    }
    setInterval(pollAgentDashboard, 5000);
});
</script>

