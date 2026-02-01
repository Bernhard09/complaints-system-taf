<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Submit Complaint
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto mt-6">
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('complaints.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium">
                    Contract Number
                </label>
                <input
                    type="text"
                    name="contract_number"
                    value="{{ old('contract_number') }}"
                    class="w-full border rounded p-2"
                    required
                >
                @error('contract_number')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">
                    Complaint Reason
                </label>
                <input
                    type="text"
                    name="complaint_reason"
                    value="{{ old('complaint_reason') }}"
                    class="w-full border rounded p-2"
                    required
                >
                @error('complaint_reason')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">
                    Description
                </label>
                <textarea
                    name="description"
                    rows="4"
                    class="w-full border rounded p-2"
                    required
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Submit Complaint
            </button>
        </form>
    </div>
</x-app-layout>
