<x-app-layout>
    <x-slot name="header">
        Supervisor Dashboard
    </x-slot>

    <div class="w-full px-10 py-8 bg-gradient-to-b from-gray-50 to-white">


        <div class="flex gap-6 overflow-x-auto pb-4">
            @foreach($columns as $status => $label)


            <div class="bg-white  rounded-2xl p-4 border border-gray-200 ">

                {{-- Column Header --}}
                <div class="flex items-center justify-between
                            border-b border-gray-100 pb-3 mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 tracking-tight">
                        {{ $label }}
                    </h3>

                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full font-medium">
                        {{ $board[$status]->count() }}
                    </span>
                </div>

                {{-- Cards --}}

                <div class="h-1 rounded-t-2xl
                    @if($status === 'SUBMITTED') bg-amber-500
                    @elseif($status === 'ASSIGNED') bg-indigo-500
                    @elseif($status === 'IN_PROGRESS') bg-blue-500
                    @elseif($status === 'WAITING_USER') bg-yellow-500
                    @elseif($status === 'RESOLVED') bg-emerald-500
                    @endif">
                </div>


                <div class=" min-w-[320px] rounded-2xl p-4 flex flex-col space-y-3 max-h-[70vh] overflow-y-auto pr-2" >
                        @forelse($board[$status] as $complaint)

                        <x-ui.card class="p-4 bg-gray-50/60 hover:bg-white hover:shadow-lg transition-all duration-200
                                        cursor-pointer
                                        border border-gray-100
                                        hover:border-indigo-200">
                            <div class="flex justify-between items-start">
                                <span class="text-xs text-gray-400 font-medium">
                                    #{{ $complaint->id }}
                                </span>

                                @if($complaint->agent)
                                    <span class="text-indigo-500 text-xs font-medium">
                                        {{ $complaint->agent->name }}
                                    </span>
                                @else
                                    <span class="text-xs font-medium text-orange-500">
                                        Unassigned
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 text-sm font-semibold text-gray-900">
                                {{ $complaint->complaint_reason }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                {{ $complaint->user->name }}
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
                                <span>{{ $complaint->created_at->diffForHumans() }}</span>
                                <a href="{{ route('supervisor.complaints.show', $complaint) }}"
                                    class="text-indigo-500 hover:underline">
                                    View Details
                                </a>

                            </div>

                        </x-ui.card>

                        @empty
                        <div class="text-xs text-gray-400">
                            No complaints
                        </div>
                        @endforelse


                </div>
            </div>
            @endforeach

    </div>


    </div>
</x-app-layout>
