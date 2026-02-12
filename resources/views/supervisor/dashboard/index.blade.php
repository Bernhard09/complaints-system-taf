@php
    $responseBreachedIds = $responseBreached;
    $resolutionBreachedIds = $resolutionBreached;
@endphp

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
                    <th class="border p-2">Assign Agent</th>
                    <th class="border p-2">SLA</th>


                </tr>
            </thead>
            <tbody>
                @foreach($complaints as $complaint)
                    <tr class="
                            @if(in_array($complaint->id, $resolutionBreachedIds))
                                bg-red-100
                            @elseif(in_array($complaint->id, $responseBreachedIds))
                                bg-yellow-100
                            @endif
                    ">
                        {{-- ID --}}
                        <td class="border p-2">{{ $complaint->id }}</td>
                        {{-- Contract Number --}}
                        <td class="border p-2">{{ $complaint->contract_number }}</td>
                        {{-- Complaint Reason   --}}
                        <td class="border p-2">{{ $complaint->complaint_reason }}</td>
                        {{-- Status --}}
                        <td class="border p-2">
                            <span class="px-2 py-1 text-sm rounded bg-yellow-100">
                                {{ $complaint->status }}
                            </span>
                        </td>
                        {{-- Assign Department --}}
                        <td class="border p-2">
                            @if(is_null($complaint->department_id))
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
                                        <button type="submit"
                                                class="bg-blue-600 text-white px-3 py-1 rounded">
                                            Assign
                                        </button>
                                    </div>
                                </form>
                            @else
                                {{ $complaint->department->name ?? '—' }}
                            @endif
                        </td>
                        {{-- Assign Agent --}}
                        <td class="border p-2">
                            @if(!is_null($complaint->department_id) && is_null($complaint->agent_id))
                                <form method="POST"
                                        action="{{ route('supervisor.complaints.assignAgent', $complaint) }}">
                                    @csrf
                                    <div class="flex gap-2">
                                        <select name="agent_id"
                                                class="border rounded p-1"
                                                required>
                                            <option value="">Select Agent</option>
                                            @foreach($agents as $agent)
                                                @if($agent->department_id === $complaint->department_id)
                                                    <option value="{{ $agent->id }}">
                                                        {{ $agent->name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                                class="bg-blue-600 text-white px-3 py-1 rounded">
                                            Assign
                                        </button>
                                    </div>
                                </form>
                            @elseif(!is_null($complaint->agent_id))
                                {{ $complaint->agent->name ?? '—' }}
                            @else
                                —
                            @endif
                        </td>
                        {{-- SLA Status --}}
                        <td class="border p-2 text-sm">
                            @if(in_array($complaint->id, $resolutionBreachedIds))
                                <span class="px-2 py-1 rounded bg-red-600 text-white">
                                    RESOLUTION SLA BREACHED
                                </span>
                            @elseif(in_array($complaint->id, $responseBreachedIds))
                                <span class="px-2 py-1 rounded bg-yellow-500 text-black">
                                    RESPONSE SLA BREACHED
                                </span>
                            @else
                                <span class="px-2 py-1 rounded bg-green-100 text-green-700">
                                    OK
                                </span>
                            @endif
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
