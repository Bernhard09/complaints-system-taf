<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            My Assigned Complaints
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto mt-6">
        <table class="w-full border-collapse border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Contract</th>
                    <th class="border p-2">Reason</th>
                    <th class="border p-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $complaint)
                    <tr>
                        <td class="border p-2">{{ $complaint->id }}</td>
                        <td class="border p-2">{{ $complaint->contract_number }}</td>
                        <td class="border p-2">{{ $complaint->complaint_reason }}</td>
                        <td class="border p-2">{{ $complaint->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"
                            class="border p-4 text-center text-gray-500">
                            No assigned complaints.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
