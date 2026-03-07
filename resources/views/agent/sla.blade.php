<x-app-layout>
    <x-slot name="header">
        SLA Monitor
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-6 sm:py-10">
        <div class="max-w-screen-2xl mx-auto space-y-6">

            {{-- METRICS --}}
            <div class="flex flex-wrap gap-4">

                <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-red-500 flex-1 min-w-[160px]">
                    <p class="text-xs text-gray-500">Response SLA Breached</p>
                    <p class="text-2xl font-semibold text-red-500">
                        {{ $metrics['response_breached'] }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-orange-500 flex-1 min-w-[160px]">
                    <p class="text-xs text-gray-500">Resolution SLA Breached</p>
                    <p class="text-2xl font-semibold text-orange-500">
                        {{ $metrics['resolution_breached'] }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-amber-500 flex-1 min-w-[160px]">
                    <p class="text-xs text-gray-500">Critical (&lt; 4h)</p>
                    <p class="text-2xl font-semibold text-amber-500">
                        {{ $metrics['critical'] }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-yellow-500 flex-1 min-w-[160px]">
                    <p class="text-xs text-gray-500">Warning (&lt; 12h)</p>
                    <p class="text-2xl font-semibold text-yellow-500">
                        {{ $metrics['warning'] }}
                    </p>
                </div>

            </div>

            {{-- RESPONSE SLA TABLE --}}
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="h-1 bg-red-500"></div>
                <div class="p-6 space-y-4">

                    <h2 class="text-lg font-semibold text-gray-800">
                        Response SLA
                        <span class="text-sm font-normal text-gray-500">— First response deadline</span>
                    </h2>

                    <div class="overflow-x-auto rounded-xl border">
                        <table class="w-full text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-6 py-3 text-left">ID</th>
                                    <th class="px-6 py-3 text-left">Contract</th>
                                    <th class="px-6 py-3 text-left">Reason</th>
                                    <th class="px-6 py-3 text-left">User</th>
                                    <th class="px-6 py-3 text-left">Deadline</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($responseTickets as $ticket)
                                    @php
                                        $isBreached = $ticket->sla_response_deadline
                                            && now()->greaterThan($ticket->sla_response_deadline);
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">#{{ $ticket->id }}</td>
                                        <td class="px-6 py-4">{{ $ticket->contract_number ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $ticket->complaint_reason }}</td>
                                        <td class="px-6 py-4">{{ $ticket->user->name }}</td>
                                        <td class="px-6 py-4">
                                            {{ $ticket->sla_response_deadline->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                                {{ $isBreached
                                                    ? 'bg-red-100 text-red-600'
                                                    : 'bg-amber-100 text-amber-600' }}">
                                                {{ $isBreached ? 'BREACHED' : 'AT RISK' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-indigo-600">
                                            <a href="{{ route('complaints.show', $ticket) }}">
                                                View →
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                                            No response SLA issues. You're on track! 🎉
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- RESOLUTION SLA TABLE --}}
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="h-1 bg-orange-500"></div>
                <div class="p-6 space-y-4">

                    <h2 class="text-lg font-semibold text-gray-800">
                        Resolution SLA
                        <span class="text-sm font-normal text-gray-500">— Task completion deadline</span>
                    </h2>

                    <div class="overflow-x-auto rounded-xl border">
                        <table class="w-full text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-6 py-3 text-left">ID</th>
                                    <th class="px-6 py-3 text-left">Contract</th>
                                    <th class="px-6 py-3 text-left">Reason</th>
                                    <th class="px-6 py-3 text-left">User</th>
                                    <th class="px-6 py-3 text-left">Deadline</th>
                                    <th class="px-6 py-3 text-left">SLA Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($resolutionTickets as $ticket)
                                    @php
                                        $sla = $ticket->sla_status;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">#{{ $ticket->id }}</td>
                                        <td class="px-6 py-4">{{ $ticket->contract_number ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $ticket->complaint_reason }}</td>
                                        <td class="px-6 py-4">{{ $ticket->user->name }}</td>
                                        <td class="px-6 py-4">
                                            {{ $ticket->sla_resolution_deadline->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                                @if($sla==='BREACHED') bg-red-100 text-red-600
                                                @elseif($sla==='CRITICAL') bg-orange-100 text-orange-600
                                                @elseif($sla==='WARNING') bg-yellow-100 text-yellow-600
                                                @else bg-green-100 text-green-600
                                                @endif">
                                                {{ $sla }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-indigo-600">
                                            <a href="{{ route('complaints.show', $ticket) }}">
                                                View →
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                                            No resolution SLA issues. You're on track! 🎉
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
