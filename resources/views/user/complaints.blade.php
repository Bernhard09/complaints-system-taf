<x-app-layout>
    <x-slot name="header">
        My Complaints
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-10">
        <div class="max-w-screen-2xl mx-auto space-y-6">

            {{-- FILTERS (Horizontal on Desktop, Stacked on Mobile) --}}
            <div class="w-full">
                <div class="bg-gray-50 border rounded-2xl p-6">
                    <form method="GET" action="{{ route('user.complaints') }}" class="flex flex-col lg:flex-row gap-4 items-end">
                        
                        {{-- Search --}}
                        <div class="w-full lg:flex-1">
                            <label class="block text-sm text-gray-600 mb-1.5">Search</label>
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search contract or reason..."
                                   class="w-full border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>

                        {{-- From --}}
                        <div class="w-full lg:flex-1">
                            <label class="block text-sm text-gray-600 mb-1.5">From</label>
                            <input type="date"
                                   name="from"
                                   value="{{ request('from') }}"
                                   class="w-full border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700" />
                        </div>

                        {{-- To --}}
                        <div class="w-full lg:flex-1">
                            <label class="block text-sm text-gray-600 mb-1.5">To</label>
                            <input type="date"
                                   name="to"
                                   value="{{ request('to') }}"
                                   class="w-full border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700" />
                        </div>

                        {{-- Buttons --}}
                        <div class="w-full lg:w-auto flex flex-col sm:flex-row gap-3 pt-2 lg:pt-0">
                            <button type="submit" class="w-full sm:w-auto px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl py-2.5 text-sm transition text-center whitespace-nowrap">
                                Apply
                            </button>
                            
                            @if(request()->hasAny(['search','from','to']))
                                <a href="{{ route('user.complaints') }}" class="w-full sm:w-auto px-4 py-2.5 text-center text-sm font-medium text-gray-500 hover:text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition whitespace-nowrap">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- COMPLAINT CARDS --}}
            <div class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

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

                            <span data-complaint-id="{{ $complaint->id }}" data-complaint-status="{{ $complaint->status }}">
                                <x-ui.status-badge :status="$complaint->status" />
                            </span>
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
                                <span class="text-xs px-2 py-0.5 rounded-full" data-sla-id="{{ $complaint->id }}" data-sla-status="{{ $sla }}"
                                    @if($sla==='BREACHED') style="background:#fee2e2;color:#dc2626"
                                    @elseif($sla==='CRITICAL') style="background:#ffedd5;color:#ea580c"
                                    @elseif($sla==='WARNING') style="background:#fef9c3;color:#ca8a04"
                                    @else style="background:#dcfce7;color:#16a34a"
                                    @endif>
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

    {{-- Async polling for status updates --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusColorMap = {
            'SUBMITTED':     { bg: '#f3f4f6', text: '#4b5563' },
            'ASSIGNED':      { bg: '#e0e7ff', text: '#4338ca' },
            'IN_PROGRESS':   { bg: '#e0e7ff', text: '#4338ca' },
            'WAITING_USER':  { bg: '#fef3c7', text: '#b45309' },
            'WAITING_CONFIRMATION': { bg: '#f3f4f6', text: '#4b5563' },
            'RESOLVED':      { bg: '#d1fae5', text: '#065f46' },
            'CANCELLED':     { bg: '#fee2e2', text: '#991b1b' },
        };
        const slaColorMap = {
            'BREACHED': { bg: '#fee2e2', text: '#dc2626' },
            'CRITICAL': { bg: '#ffedd5', text: '#ea580c' },
            'WARNING':  { bg: '#fef9c3', text: '#ca8a04' },
            'ON_TRACK': { bg: '#dcfce7', text: '#16a34a' },
        };

        async function pollComplaints() {
            try {
                const resp = await fetch('/api/poll/user-complaints');
                if (!resp.ok) return;
                const data = await resp.json();

                if (data.complaints) {
                    data.complaints.forEach(c => {
                        // Update status badges
                        const statusEl = document.querySelector(`[data-complaint-id="${c.id}"]`);
                        if (statusEl && statusEl.dataset.complaintStatus !== c.status) {
                            const badge = statusEl.querySelector('span');
                            if (badge) {
                                const colors = statusColorMap[c.status] || statusColorMap['SUBMITTED'];
                                badge.style.backgroundColor = colors.bg;
                                badge.style.color = colors.text;
                                badge.textContent = c.status.replace(/_/g, ' ');
                            }
                            statusEl.dataset.complaintStatus = c.status;

                            // Flash
                            statusEl.style.transition = 'transform .3s';
                            statusEl.style.transform = 'scale(1.15)';
                            setTimeout(() => statusEl.style.transform = 'scale(1)', 400);
                        }

                        // Update SLA badges
                        const slaEl = document.querySelector(`[data-sla-id="${c.id}"]`);
                        if (slaEl && c.sla_status && slaEl.dataset.slaStatus !== c.sla_status) {
                            const slaColors = slaColorMap[c.sla_status] || slaColorMap['ON_TRACK'];
                            slaEl.style.backgroundColor = slaColors.bg;
                            slaEl.style.color = slaColors.text;
                            slaEl.textContent = `SLA: ${c.sla_status}`;
                            slaEl.dataset.slaStatus = c.sla_status;
                        }
                    });
                }
            } catch (e) {}
        }
        setInterval(pollComplaints, 5000);
    });
    </script>
</x-app-layout>

