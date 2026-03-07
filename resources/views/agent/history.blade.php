<x-app-layout>
    <x-slot name="header">
        Complaint History
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-6 sm:py-10">
        <div class="max-w-6xl mx-auto space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border p-6">

                <form method="GET" class="bg-gray-50 border rounded-xl p-4 mb-6">
                    <div class="flex flex-col md:flex-row gap-4 md:items-end">
                        <div class="w-full md:w-64">
                            <label class="text-xs text-gray-500 block mb-1">Search</label>
                            <input type="text"
                                   name="search"
                                   placeholder="Search contract or reason..."
                                   value="{{ request('search') }}"
                                   class="border rounded-lg px-3 py-2 text-sm w-full focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 block mb-1">From</label>
                            <input type="date"
                                   name="from"
                                   value="{{ request('from') }}"
                                   class="border rounded-lg px-3 py-2 text-sm w-full focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 block mb-1">To</label>
                            <input type="date"
                                   name="to"
                                   value="{{ request('to') }}"
                                   class="border rounded-lg px-3 py-2 text-sm w-full focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm w-full md:w-auto h-[38px]">
                                Apply
                            </button>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto rounded-xl border">
                    <table class="w-full text-sm whitespace-nowrap">
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
