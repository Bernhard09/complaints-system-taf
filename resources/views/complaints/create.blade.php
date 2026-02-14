<x-app-layout>
    <x-slot name="header">
        Submit Complaint
    </x-slot>



    <div class="mx-auto w-full max-w-5xl px-10 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            {{-- LEFT: FROM --}}
            <div class="lg:col-span-2">

                <x-ui.card class="p-10 space-y-10">

                         @if(session('success'))
                                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                                    {{ session('success') }}
                                </div>
                        @endif

                    <div>
                        <h2 class="text-xl font-semibold">
                            Create New Complaint
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Provide details below so our team can assist you.
                        </p>
                    </div>

                    {{-- <div class="bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-xl p-4 text-sm">
                        Our support team typically responds within 24 hours.
                    </div> --}}

                    <div class="border-t border-gray-100"></div>


                    <form method="POST" action="{{ route('complaints.store') }}" class="space-y-6">
                        @csrf

                        {{-- Contract --}}
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">
                                Contract Number
                            </label>
                            <p class="text-xs text-gray-400">
                                Enter the contract number related to this issue.
                            </p>
                            <input type="text"
                                    name="contract_number"
                                    class="w-full rounded-xl border-gray-300 shadow-sm
                                            focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                            transition duration-200"
                                    required>
                        </div>

                        {{-- Reason --}}
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">
                                Complaint Reason
                            </label>
                            <p class="text-xs text-gray-400">
                                Enter the reason for your complaint.
                            </p>
                            <input type="text"
                                    name="complaint_reason"
                                    class="w-full rounded-xl border-gray-300 shadow-sm
                                            focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                            transition duration-200"
                                    required>

                        </div>

                        {{-- Description --}}
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <p class="text-xs text-gray-400">
                                Provide a detailed description of the issue you're facing.
                            </p>
                            <textarea name="description"
                                        rows="8"
                                        class="w-full rounded-xl border-gray-300 shadow-sm
                                                focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                                transition duration-200 resize-none"
                                        required></textarea>
                        </div>

                        {{-- Button --}}
                        <div class="pt-6 flex items-center justify-between">
                            <p class="text-xs text-gray-400">
                                By submitting, you agree to our support handling policy.
                            </p>

                            <x-ui.button class="px-8 py-3 rounded-xl text-sm font-medium">
                                Submit Complaint
                            </x-ui.button>
                        </div>

                    </form>

                </x-ui.card>
            </div>

            {{-- RIGHT: CONTEXT --}}
            <div class="space-y-6">
                <x-ui.card class="p-6 space-y-3">
                    <h3 class="font-semibold">Response Time</h3>
                    <p class="text-sm text-gray-500">
                        Our support team typically responds within 24 hours.
                    </p>
                </x-ui.card>

                <x-ui.card class="p-6 space-y-3">
                    <h3 class="font-semibold">What happens next?</h3>

                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• Complaint is reviewed</li>
                        <li>• Assigned to support agent</li>
                        <li>• You’ll receive updates via dashboard</li>
                    </ul>
                </x-ui.card>

                <x-ui.card class="p-6 space-y-3 bg-indigo-50 border-indigo-100">
                    <h3 class="font-semibold text-indigo-700">
                        Need urgent help?
                    </h3>
                    <p class="text-sm text-indigo-600">
                        Make sure your description clearly explains the issue.
                    </p>
                </x-ui.card>


            </div>
        </div>

    </div>
</x-app-layout>
