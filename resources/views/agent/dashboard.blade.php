<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">
            Agent Workspace
        </h2>
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-10">
        <div class="max-w-screen-2xl mx-auto px-10 space-y-8">

            {{-- ================= OVERVIEW ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @php
                    $overviewCards = [
                        ['label'=>'Active','value'=>$metrics['active'],'color'=>'indigo'],
                        ['label'=>'Waiting User','value'=>$metrics['waiting'],'color'=>'amber'],
                        ['label'=>'Breached SLA','value'=>$metrics['breached'],'color'=>'red'],
                        ['label'=>'Resolved Today','value'=>$metrics['resolved_today'],'color'=>'green'],
                    ];
                @endphp

                @foreach($overviewCards as $card)
                    <div class="bg-white rounded-2xl shadow-sm border p-6">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">
                            {{ $card['label'] }}
                        </p>
                        <p class="text-3xl font-bold mt-2 text-{{ $card['color'] }}-600">
                            {{ $card['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>



            {{-- ================= SLA MONITOR ================= --}}
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">
                        SLA Monitoring
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
                            No SLA issues detected.
                        </div>
                    @endforelse
                </div>
            </div>



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

                                <div class="w-80 bg-gray-50 rounded-xl p-4 border">

                                    <div class="flex justify-between mb-4">
                                        <h3 class="text-sm font-semibold text-gray-700">
                                            {{ $label }}
                                        </h3>
                                        <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">
                                            {{ $board[$status]->count() ?? 0 }}
                                        </span>
                                    </div>

                                    <div class="space-y-4">

                                        @foreach($board[$status] ?? [] as $ticket)

                                            <div class="bg-white rounded-lg p-4 shadow-sm border hover:shadow-md transition">

                                                <div class="text-xs text-gray-400 flex justify-between">
                                                    <span>#{{ $ticket->id }}</span>

                                                    @if($ticket->sla_status==='BREACHED')
                                                        <span class="text-red-600 font-semibold">
                                                            BREACHED
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-2 font-medium text-sm">
                                                    {{ $ticket->complaint_reason }}
                                                </div>

                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $ticket->user->name }}
                                                </div>

                                                <div class="mt-3 text-xs text-indigo-600">
                                                    <a href="{{ route('complaints.show',$ticket) }}">
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
                                    {{ $status }}
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
                        <div class="overflow-hidden rounded-xl border">
                            <table class="w-full text-sm">
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
                                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $color }}">
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
