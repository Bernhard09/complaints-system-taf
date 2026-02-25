<x-app-layout>
    <x-slot name="header">
        My Complaints
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-10">
        <div class="max-w-screen-2xl mx-auto px-8 space-y-6">

            {{-- FILTERS --}}
            <x-ui.card>
                <form method="GET" action="{{ route('user.complaints') }}"
                      class="flex flex-wrap gap-4 items-end">

                    {{-- Search --}}
                    <div class="flex-1 min-w-[200px]">
                        <label class="text-xs text-gray-500">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Contract, reason, or description..."
                               class="w-full mt-1 border rounded-lg px-3 py-2 text-sm" />
                    </div>

                    {{-- Status --}}
                    <div class="min-w-[160px]">
                        <label class="text-xs text-gray-500">Status</label>
                        <select name="status"
                                class="w-full mt-1 border rounded-lg px-3 py-2 text-sm">
                            <option value="ALL">All Status</option>
                            @foreach(['SUBMITTED','ASSIGNED','IN_PROGRESS','WAITING_USER','WAITING_CONFIRMATION','RESOLVED','CLOSED'] as $s)
                                <option value="{{ $s }}"
                                    {{ request('status') === $s ? 'selected' : '' }}>
                                    {{ $s }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- From --}}
                    <div>
                        <label class="text-xs text-gray-500">From</label>
                        <input type="date"
                               name="from"
                               value="{{ request('from') }}"
                               class="mt-1 border rounded-lg px-3 py-2 text-sm" />
                    </div>

                    {{-- To --}}
                    <div>
                        <label class="text-xs text-gray-500">To</label>
                        <input type="date"
                               name="to"
                               value="{{ request('to') }}"
                               class="mt-1 border rounded-lg px-3 py-2 text-sm" />
                    </div>

                    <div class="flex gap-2">
                        <x-ui.button>Filter</x-ui.button>

                        @if(request()->hasAny(['search','status','from','to']))
                            <a href="{{ route('user.complaints') }}"
                               class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">
                                Clear
                            </a>
                        @endif
                    </div>

                </form>
            </x-ui.card>

            {{-- COMPLAINT CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                @forelse($complaints as $complaint)
                    <x-ui.card>
                        <div class="flex justify-between items-start">
                            <div class="space-y-1">
                                <p class="text-xs text-gray-400 uppercase">Contract</p>
                                <p class="font-semibold text-lg">
                                    {{ $complaint->contract_number }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ $complaint->complaint_reason }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $complaint->created_at->format('d M Y H:i') }}
                                    · {{ $complaint->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <x-ui.status-badge :status="$complaint->status" />
                        </div>

                        @if($complaint->description)
                            <p class="text-sm text-gray-500 mt-3 line-clamp-2">
                                {{ $complaint->description }}
                            </p>
                        @endif

                        <div class="mt-4 border-t pt-3 flex justify-between items-center">
                            <a href="{{ route('complaints.show', $complaint) }}"
                               class="text-sm text-indigo-600 hover:underline font-medium">
                                Open →
                            </a>

                            @if($complaint->sla_resolution_deadline)
                                @php $sla = $complaint->sla_status; @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    @if($sla==='BREACHED') bg-red-100 text-red-600
                                    @elseif($sla==='CRITICAL') bg-orange-100 text-orange-600
                                    @elseif($sla==='WARNING') bg-yellow-100 text-yellow-600
                                    @else bg-green-100 text-green-600
                                    @endif">
                                    SLA: {{ $sla }}
                                </span>
                            @endif
                        </div>
                    </x-ui.card>
                @empty
                    <div class="col-span-2">
                        <x-ui.card>
                            <div class="text-center py-8">
                                <p class="text-gray-500">No complaints found.</p>
                                <a href="{{ route('complaints.create') }}"
                                   class="text-indigo-600 text-sm mt-2 inline-block hover:underline">
                                    Submit your first complaint →
                                </a>
                            </div>
                        </x-ui.card>
                    </div>
                @endforelse

            </div>

            {{-- PAGINATION --}}
            @if($complaints->hasPages())
                <div class="mt-6">
                    {{ $complaints->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
