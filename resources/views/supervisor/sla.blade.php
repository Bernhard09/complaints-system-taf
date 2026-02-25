<x-app-layout>
    <x-slot name="header">
        SLA Settings
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-10">
        <div class="max-w-xl mx-auto px-6">

            <div class="bg-white rounded-2xl shadow-sm border p-6 space-y-6">

                <form method="POST" action="{{ route('supervisor.sla.update') }}">
                    @csrf

                    <div>
                        <label class="text-sm text-gray-600">
                            First Response SLA (hours)
                        </label>
                        <input type="number"
                               name="first_response_hours"
                               value="{{ $sla->first_response_hours ?? 2 }}"
                               class="w-full mt-2 border rounded-lg px-3 py-2">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">
                            Resolution SLA (hours)
                        </label>
                        <input type="number"
                                name="resolution_hours"
                                value="{{ $sla->resolution_hours ?? 24 }}"
                                class="w-full mt-2 border rounded-lg px-3 py-2">
                    </div>

                    <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg">
                        Save
                    </button>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>
