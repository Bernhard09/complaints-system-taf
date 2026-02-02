<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Supervisor – Incoming Complaints
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto mt-6">
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full border-collapse border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Contract</th>
                    <th class="border p-2">Reason</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Assign Department</th>
                </tr>
            </thead>
            <tbody>
                @foreach($complaints as $complaint)
                    <tr>
                        <td class="border p-2">{{ $complaint->id }}</td>
                        <td class="border p-2">{{ $complaint->contract_number }}</td>
                        <td class="border p-2">{{ $complaint->complaint_reason }}</td>
                        <td class="border p-2">
                            <span class="px-2 py-1 text-sm rounded bg-yellow-100">
                                {{ $complaint->status }}
                            </span>
                        </td>
                        <td class="border p-2">
                            <form method="POST"
                                    action="{{ route('supervisor.complaints.assign', $complaint) }}">
                                @csrf
                                <div class="flex gap-2">
                                    <select name="department_id"
                                            class="border rounded p-1"
                                            required>
                                        <option value="">Select</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button
                                        type="submit"
                                        class="bg-blue-600 text-white px-3 py-1 rounded">
                                        Assign
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
