<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="mx-auto w-full max-w-screen-2xl space-y-6 sm:space-y-10">

        {{-- ROW 1: METRICS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <x-ui.metric-card
                title="Ongoing"
                :value="$ongoing"
                color="indigo"
                pollKey="ongoing"
            />

            <x-ui.metric-card
                title="Waiting"
                :value="$waiting"
                color="amber"
                pollKey="waiting"
            />

            <x-ui.metric-card
                title="Resolved"
                :value="$resolved"
                color="emerald"
                pollKey="resolved"
            />

        </div>

        {{-- NOTIFICATIONS --}}
        @include('partials.notification-card')

        {{-- ROW 2: ACTION CARD --}}
        <x-ui.card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">
                        Need assistance?
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Submit a new complaint and our team will assist you.
                    </p>
                </div>

                <a href="{{ route('complaints.create') }}">
                    <x-ui.button>
                        Submit Complaint
                    </x-ui.button>
                </a>
            </div>
        </x-ui.card>

        {{-- ROW 3: ACTIVE COMPLAINT --}}
        @if($ongoing > 0)
        <x-ui.card>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Active Complaint</p>
                    <p class="text-xl font-semibold mt-1">
                        {{ $recent->first()->contract_number ?? '-' }}
                    </p>
                    <p class="text-sm text-gray-600 mt-2">
                        {{ $recent->first()->complaint_reason ?? '-' }}
                    </p>
                </div>

                <span data-complaint-id="{{ $recent->first()->id }}">
                    <x-ui.status-badge :status="$recent->first()->status ?? '-' " />
                </span>
            </div>

            <div class="mt-4">
                <a href="{{ route('complaints.show', $recent->first() ?? '-') }}"
                class="text-indigo-600 text-sm font-medium">
                    Open →
                </a>
            </div>
        </x-ui.card>
        @endif

        {{-- @if($ongoing > 0)
        <x-ui.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">
                        Active Complaint
                    </p>

                    <p class="font-semibold mt-1">
                        {{ $recent->first()->contract_number ?? '-' }}
                    </p>
                </div>

                <x-ui.status-badge :status="$complaint->status" />
            </div>
        </x-ui.card>
        @endif --}}


        {{-- ROW 3: RECENT HISTORY --}}
        <div>
            <h3 class="text-lg font-semibold mb-4">
                Recent Complaints
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                @forelse($recent as $complaint)
                <x-ui.card>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-xs text-gray-400 uppercase">
                                Contract
                            </p>

                            <p class="font-semibold">
                                {{ $complaint->contract_number }}
                            </p>

                            <p class="text-sm text-gray-600 mt-2">
                                {{ $complaint->complaint_reason ?? '-' }}
                            </p>

                            <p class="text-xs text-gray-400 mt-1">
                                {{ $complaint->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <span data-complaint-id="{{ $complaint->id }}">
                            <x-ui.status-badge :status="$complaint->status" />
                        </span>
                    </div>

                    <div class="mt-4 border-t pt-3 flex justify-between items-center">
                        <a href="{{ route('complaints.show', $complaint) }}"
                        class="text-sm text-indigo-600 hover:underline">
                            Open →
                        </a>
                    </div>
                </x-ui.card>
                @empty
                    <x-ui.card>
                        <p class="text-sm text-gray-500">
                            No complaints yet.
                        </p>
                    </x-ui.card>
                @endforelse

            </div>
        </div>

    </div>

    {{-- Dashboard polling script --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusColors = {
            'SUBMITTED':              { bg: '#f3f4f6', text: '#4b5563' },
            'IN_REVIEW':              { bg: '#fef3c7', text: '#92400e' },
            'ASSIGNED':               { bg: '#f3f4f6', text: '#4b5563' },
            'IN_PROGRESS':            { bg: '#e0e7ff', text: '#4338ca' },
            'WAITING_USER':           { bg: '#fef3c7', text: '#b45309' },
            'WAITING_CONFIRMATION':   { bg: '#f3f4f6', text: '#4b5563' },
            'RESOLVED':               { bg: '#d1fae5', text: '#065f46' },
            'CANCELLED':              { bg: '#fee2e2', text: '#991b1b' },
        };

        async function pollDashboard() {
            try {
                const resp = await fetch('/api/poll/user-dashboard');
                if (!resp.ok) return;
                const data = await resp.json();

                // Update metric cards
                document.querySelectorAll('[data-poll-key]').forEach(el => {
                    const key = el.dataset.pollKey;
                    if (data[key] !== undefined && el.textContent.trim() !== String(data[key])) {
                        el.textContent = data[key];
                        el.style.transition = 'color .3s';
                        el.style.color = '#4f46e5';
                        setTimeout(() => el.style.color = '', 800);
                    }
                });

                // Update complaint status badges
                if (data.recent) {
                    data.recent.forEach(c => {
                        const wrapper = document.querySelector(`[data-complaint-id="${c.id}"]`);
                        if (!wrapper) return;
                        const badge = wrapper.querySelector('span');
                        if (!badge) return;
                        const displayStatus = c.status.replace(/_/g, ' ');
                        if (badge.textContent.trim() !== displayStatus) {
                            const colors = statusColors[c.status] || statusColors['SUBMITTED'];
                            badge.textContent = displayStatus;
                            badge.style.backgroundColor = colors.bg;
                            badge.style.color = colors.text;
                            badge.style.transition = 'transform .3s';
                            badge.style.transform = 'scale(1.15)';
                            setTimeout(() => badge.style.transform = 'scale(1)', 400);
                        }
                    });
                }
            } catch (e) {}
        }
        setInterval(pollDashboard, 5000);
        pollDashboard();
    });
    </script>
</x-app-layout>
