<x-app-layout>
    <x-slot name="header">
        Complaint History
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-10">
        <div class="max-w-screen-2xl mx-auto px-10 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border p-6 space-y-6">

                {{-- FILTER --}}
                <form method="GET" class="bg-gray-50 border rounded-xl p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                        <div>
                            <label class="text-xs text-gray-500">Search</label>
                            <input type="text"
                                   name="search"
                                   placeholder="Contract, reason, or user..."
                                   value="{{ request('search') }}"
                                   class="w-full mt-1 border rounded-lg px-3 py-2 text-sm
                                          focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">From</label>
                            <input type="date"
                                   name="from"
                                   value="{{ request('from') }}"
                                   class="w-full mt-1 border rounded-lg px-3 py-2 text-sm
                                          focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">To</label>
                            <input type="date"
                                   name="to"
                                   value="{{ request('to') }}"
                                   class="w-full mt-1 border rounded-lg px-3 py-2 text-sm
                                          focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">Department</label>
                            <select name="department"
                                    class="w-full mt-1 border rounded-lg px-3 py-2 text-sm
                                           focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ request('department') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm">
                                Apply
                            </button>

                            <a href="{{ route('supervisor.history') }}"
                               class="px-4 py-2 rounded-lg bg-gray-200 text-sm">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>

                {{-- TABLE --}}
                <div class="overflow-hidden rounded-xl border">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left">ID</th>
                                <th class="px-6 py-3 text-left">Contract</th>
                                <th class="px-6 py-3 text-left">Reason</th>
                                <th class="px-6 py-3 text-left">User</th>
                                <th class="px-6 py-3 text-left">Agent</th>
                                <th class="px-6 py-3 text-left">Department</th>
                                <th class="px-6 py-3 text-left">Submitted</th>
                                <th class="px-6 py-3 text-left">Resolved</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th></th>
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

                                    <td class="px-6 py-4 text-gray-600">
                                        {{ optional($ticket->agent)->name ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-600">
                                        {{ optional($ticket->department)->name ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-500">
                                        {{ $ticket->created_at->format('d M Y H:i') }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-500">
                                        {{ optional($ticket->resolved_at)->format('d M Y H:i') ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            {{ $ticket->status === 'RESOLVED'
                                                ? 'bg-green-100 text-green-600'
                                                : 'bg-gray-100 text-gray-600' }}">
                                            {{ str_replace('_', ' ', $ticket->status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-indigo-600">
                                        <a href="{{ route('supervisor.complaints.show', $ticket) }}">
                                            View →
                                        </a>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10"
                                        class="px-6 py-6 text-center text-gray-500">
                                        No resolved complaints.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div>
                    {{ $tickets->links() }}
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
