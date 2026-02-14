<x-app-layout>
    <x-slot name="header">
        User Dashboard
    </x-slot>

    <div class="mx-auto w-full max-w-screen-2xl px-8 py-8 space-y-10">

        {{-- ROW 1: METRICS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <x-ui.metric-card
                title="Ongoing"
                :value="$ongoing"
                color="indigo"
                {{-- :link="route('complaints.index', ['status' => 'IN_PROGRESS'])" --}}
            />

            <x-ui.metric-card
                title="Waiting"
                :value="$waiting"
                color="amber"
            />

            <x-ui.metric-card
                title="Resolved"
                :value="$resolved"
                color="emerald"
            />

        </div>

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

                <x-ui.status-badge :status="$recent->first()->status ?? '-' " />
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

                        <x-ui.status-badge :status="$complaint->status" />
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
</x-app-layout>
