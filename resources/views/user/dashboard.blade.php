<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">My Complaints</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto mt-6">

        <a href="{{ route('complaints.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded">
            Submit Complaint
        </a>

        <table class="w-full mt-6 border">
            <thead>
                <tr>
                    <th class="border p-2">Contract</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($complaints as $complaint)
                    <tr>
                        <td class="border p-2">{{ $complaint->contract_number }}</td>
                        <td class="border p-2">{{ $complaint->status }}</td>
                        <td class="border p-2">
                            <a href="{{ route('complaints.show', $complaint) }}"
                                class="text-blue-600 underline">
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</x-app-layout>
