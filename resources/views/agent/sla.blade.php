<x-app-layout>
    <x-slot name="header">
        SLA Monitor
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-10">
        <div class="max-w-6xl mx-auto px-6">

            <div class="bg-white rounded-2xl shadow-sm border p-6">

                <div class="overflow-hidden rounded-xl border">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left">ID</th>
                                <th class="px-6 py-3 text-left">Reason</th>
                                <th class="px-6 py-3 text-left">Deadline</th>
                                <th class="px-6 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td class="px-6 py-4">#{{ $ticket->id }}</td>
                                    <td class="px-6 py-4">{{ $ticket->complaint_reason }}</td>
                                    <td class="px-6 py-4">
                                        {{ $ticket->sla_resolution_deadline->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $sla = $ticket->sla_status;
                                        @endphp

                                        <span class="px-2 py-1 rounded-full text-xs
                                            @if($sla==='BREACHED') bg-red-100 text-red-600
                                            @elseif($sla==='CRITICAL') bg-orange-100 text-orange-600
                                            @elseif($sla==='WARNING') bg-yellow-100 text-yellow-600
                                            @else bg-green-100 text-green-600
                                            @endif">
                                            {{ $sla }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                                        No SLA monitored tickets.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
