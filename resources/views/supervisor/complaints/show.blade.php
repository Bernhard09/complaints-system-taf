<x-app-layout>

<div
    x-data="{
        openReassign: false,
        department: '',
        agent: '',
        departments: {{ $departments->toJson() }},
        agents: []
    }"
>

    <x-slot name="header">
        Complaint #{{ $complaint->id }}
    </x-slot>

    <div class="w-full px-10 py-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ================= LEFT SIDE ================= --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Complaint Info --}}
                <x-ui.card class="p-6">
                    <h2 class="text-lg font-semibold mb-4">
                        Complaint Information
                    </h2>

                    <div class="space-y-3 text-sm">

                        <div>
                            <span class="text-gray-500">Contract:</span>
                            <span class="font-medium">
                                {{ $complaint->contract_number }}
                            </span>
                        </div>

                        <div>
                            <span class="text-gray-500">Reason:</span>
                            <span class="font-medium">
                                {{ $complaint->complaint_reason }}
                            </span>
                        </div>

                        <div>
                            <span class="text-gray-500">Description:</span>
                            <p class="mt-1 text-gray-700">
                                {{ $complaint->description }}
                            </p>
                        </div>

                        <div>
                            <span class="text-gray-500">Submitted by:</span>
                            <span class="font-medium">
                                {{ $complaint->user->name }}
                            </span>
                        </div>

                    </div>
                </x-ui.card>


                {{-- Attachments --}}
                @if($complaint->attachments->count())
                <x-ui.card class="p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        Attachments
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                        @foreach($complaint->attachments as $file)

                            <div x-data="{ open: false }">

                                {{-- Thumbnail --}}
                                @if(Str::contains($file->mime_type, 'image'))
                                    <img
                                        src="{{ asset('storage/' . $file->file_path) }}"
                                        class="rounded-lg cursor-pointer border"
                                        @click="open = true"
                                    >
                                @else
                                    <div
                                        class="p-6 bg-gray-100 rounded-lg text-center cursor-pointer"
                                        @click="open = true"
                                    >
                                        📄 PDF File
                                    </div>
                                @endif

                                {{-- Preview Modal --}}
                                <div
                                    x-show="open"
                                    x-transition
                                    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
                                    @click="open = false"
                                >
                                    <div
                                        @click.stop
                                        class="bg-white p-4 rounded-xl max-w-3xl w-full"
                                    >

                                        @if(Str::contains($file->mime_type, 'image'))
                                            <img
                                                src="{{ asset('storage/' . $file->file_path) }}"
                                                class="w-full rounded"
                                            >
                                        @else
                                            <iframe
                                                src="{{ asset('storage/' . $file->file_path) }}"
                                                class="w-full h-[500px]"
                                            ></iframe>
                                        @endif

                                    </div>
                                </div>

                            </div>

                        @endforeach

                    </div>
                </x-ui.card>
                @endif

            </div>


            {{-- ================= RIGHT SIDE ================= --}}
            <div class="space-y-6">

                {{-- Assignment --}}
                <x-ui.card class="p-6">

                    <h3 class="text-lg font-semibold mb-4">
                        Assignment
                    </h3>

                    @if(!$complaint->agent_id && $complaint->status === 'SUBMITTED')

                        <form method="POST"
                              action="{{ route('supervisor.complaints.assign', $complaint) }}">
                            @csrf

                            {{-- Department --}}
                            <select
                                x-model="department"
                                @change="
                                    let dept = departments.find(d => d.id == department);
                                    agents = dept ? dept.agents : [];
                                    agent = '';
                                "
                                name="department_id"
                                class="w-full mb-3 rounded-lg border"
                            >
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Agent --}}
                            <select
                                name="agent_id"
                                x-model="agent"
                                class="w-full rounded-lg border"
                                required 
                            >
                                <option value="">Select Agent</option>
                                <template x-for="a in agents" :key="a.id">
                                    <option :value="a.id" x-text="a.name"></option>
                                </template>
                            </select>

                            <button
                                class="mt-4 w-full bg-indigo-600 text-white py-2 rounded-lg"
                            >
                                Assign Agent
                            </button>

                        </form>

                    @else
                        <div class="text-sm text-gray-600">
                            Assigned to:
                            <span class="font-medium">
                                {{ $complaint->agent?->name }}
                            </span>
                        </div>
                    @endif

                </x-ui.card>


                {{-- Actions --}}
                <x-ui.card class="p-6">

                    <h3 class="text-sm font-semibold mb-4">
                        Actions
                    </h3>

                    {{-- Reassign --}}
                    @if($complaint->status === 'IN_PROGRESS')
                        <button
                            @click="openReassign = true"
                            class="text-sm text-indigo-600 hover:underline"
                        >
                            Reassign
                        </button>
                    @endif

                    {{-- Reopen --}}
                    @if($complaint->status === 'RESOLVED')
                        <form method="POST"
                              action="{{ route('supervisor.complaints.reopen', $complaint) }}">
                            @csrf
                            <button
                                class="text-sm text-red-600 hover:underline"
                            >
                                Reopen Complaint
                            </button>
                        </form>
                    @endif

                    {{-- Chat Link --}}
                    <div class="mt-3">
                        @if($complaint->agent)
                            <a href="{{ route('complaints.show', $complaint) }}"
                               class="text-indigo-600 text-sm hover:underline">
                                View Conversation →
                            </a>
                        @else
                            <span class="text-gray-300 text-sm cursor-not-allowed">
                                View Conversation →
                            </span>
                        @endif
                    </div>

                </x-ui.card>


                {{-- SLA --}}
                <x-ui.card class="p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        SLA Information
                    </h3>

                    <div class="text-sm text-gray-600">
                        Status: {{ $complaint->status }}
                    </div>
                </x-ui.card>

            </div>

        </div>
    </div>


    {{-- ================= REASSIGN MODAL ================= --}}
    <div
        x-show="openReassign"
        x-transition
        class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
    >
        <div
            @click.away="openReassign = false"
            class="bg-white w-full max-w-lg rounded-2xl p-6 shadow-xl"
        >

            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold">Reassign Complaint</h3>
                <button @click="openReassign = false">✕</button>
            </div>

            <form method="POST"
                  action="{{ route('supervisor.complaints.reassign', $complaint) }}">
                @csrf

                {{-- Department --}}
                <div class="mb-4">
                    <label class="text-xs text-gray-500">Department</label>
                    <select
                        x-model="department"
                        @change="
                            let dept = departments.find(d => d.id == department);
                            agents = dept ? dept.agents : [];
                            agent = '';
                        "
                        name="department_id"
                        class="w-full rounded-lg border"
                    >
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Agent --}}
                <div class="mb-4">
                    <label class="text-xs text-gray-500">Agent</label>
                    <select
                        x-model="agent"
                        name="agent_id"
                        class="w-full rounded-lg border"
                    >
                        <option value="">Select Agent</option>
                        <template x-for="a in agents" :key="a.id">
                            <option :value="a.id" x-text="a.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Reason --}}
                <div class="mb-4">
                    <label class="text-xs text-gray-500">Reason</label>
                    <textarea
                        name="reason"
                        rows="3"
                        required
                        class="w-full rounded-lg border"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="openReassign = false"
                        class="text-gray-500"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg"
                    >
                        Confirm Reassign
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

</x-app-layout>
