<x-app-layout>
    <x-slot name="header">
        Complaint History
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-10">
        <div class="max-w-6xl mx-auto px-6 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border p-6">

                <form method="GET" class="flex gap-4 mb-6">
                    <input type="text"
                           name="search"
                           placeholder="Search contract or reason..."
                           value="{{ request('search') }}"
                           class="border rounded-lg px-3 py-2 text-sm w-64">

                    <input type="date"
                           name="from"
                           value="{{ request('from') }}"
                           class="border rounded-lg px-3 py-2 text-sm">

                    <input type="date"
                           name="to"
                           value="{{ request('to') }}"
                           class="border rounded-lg px-3 py-2 text-sm">

                    <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">
                        Apply
                    </button>
                </form>

                <div class="overflow-hidden rounded-xl border">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left">ID</th>
                                <th class="px-6 py-3 text-left">Contract</th>
                                <th class="px-6 py-3 text-left">Reason</th>
                                <th class="px-6 py-3 text-left">User</th>
                                <th class="px-6 py-3 text-left">Submitted</th>
                                <th class="px-6 py-3 text-left">Resolved</th>
                                <th class="px-6 py-3 text-left">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y bg-white">
                            @forelse($tickets as $ticket)
                                <tr class="hover:bg-gray-50">

                                    <td class="px-6 py-4 font-medium">
                                        #{{ $ticket->id }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $ticket->contract_number ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $ticket->complaint_reason }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $ticket->user->name }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-500">
                                        {{ $ticket->created_at->format('d M Y H:i') }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-500">
                                        {{ optional($ticket->resolved_at)->format('d M Y H:i') ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs
                                            {{ $ticket->status === 'RESOLVED'
                                                ? 'bg-green-100 text-green-600'
                                                : 'bg-gray-100 text-gray-600' }}">
                                            {{ $ticket->status }}
                                        </span>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="px-6 py-6 text-center text-gray-500">
                                        No resolved complaints.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $tickets->links() }}
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
