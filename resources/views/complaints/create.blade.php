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


                    <form method="POST"
                        enctype="multipart/form-data"
                        action="{{ route('complaints.store') }}"
                        class="space-y-6">
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
                            @error('contract_number')
                                <p class="text-sm text-red-500 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror

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
                            @error('complaint_reason')
                                <p class="text-sm text-red-500 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
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
                            @error('description')
                                <p class="text-sm text-red-500 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-3">
                            <label class="text-sm font-medium text-gray-700">
                                Attachments (max 10 files)
                            </label>

                            <div id="dropZone"
                                class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition">

                                <input
                                    type="file"
                                    name="attachments[]"
                                    multiple
                                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                                    class="hidden"
                                    id="fileInput"
                                >

                                <p class="text-sm text-gray-600">
                                    Drag & drop files here
                                </p>

                                <p class="text-xs text-gray-400 mt-1">
                                    or click to upload
                                </p>
                            </div>

                            <div id="filePreview" class="space-y-2 text-sm"></div>
                            <div id="file-meta" class="mt-3 text-xs text-gray-500 hidden">
                                <span id="file-count">0 files</span>
                                •
                                <span id="file-size">0 KB</span>
                            </div>
                        </div>

                        {{-- Button --}}
                        <div class="pt-6 flex items-center justify-between">
                            <p class="text-xs text-gray-400">
                                By submitting, you agree to our support handling policy.
                            </p>

                            <x-ui.button
                                id="submitBtn"
                                class="px-8 py-3 rounded-xl text-sm font-medium">
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

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const preview = document.getElementById('filePreview');

const fileMeta = document.getElementById('file-meta');
const fileCount = document.getElementById('file-count');
const fileSize = document.getElementById('file-size');

const form = document.querySelector('form');
const submitBtn = document.getElementById('submitBtn');

let selectedFiles = [];


dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');

    handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', () => {
    handleFiles(fileInput.files);
});

form.addEventListener('submit', function() {

    submitBtn.disabled = true;
    submitBtn.innerText = "Uploading...";

});

function handleFiles(files) {

    for (let file of files) {

        if (selectedFiles.length >= 10) {
            alert("Maximum 10 files allowed.");
            break;
        }

        if (!['image/jpeg','image/png','image/jpg','application/pdf'].includes(file.type)) {
            alert("Only JPG, PNG, and PDF allowed.");
            continue;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert(`${file.name} exceeds 5MB limit.`);
            continue;
        }

        selectedFiles.push(file);
    }

    updatePreview();
}


function updatePreview() {
    preview.innerHTML = '';

    const dataTransfer = new DataTransfer();

    selectedFiles.forEach((file, index) => {

        dataTransfer.items.add(file);

        const wrapper = document.createElement('div');
        wrapper.className = "flex items-center justify-between border rounded-xl p-3 bg-white shadow-sm";

        let filePreview;

        // IMAGE PREVIEW
        if (file.type.startsWith('image/')) {

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = "w-14 h-14 object-cover rounded-lg border";

            filePreview = img;

        } else if (file.type === 'application/pdf') {

            filePreview = document.createElement('div');
            filePreview.className = "w-14 h-14 flex items-center justify-center rounded-lg bg-red-100 text-red-600 text-xs font-semibold";
            filePreview.innerText = "PDF";

        } else {

            filePreview = document.createElement('div');
            filePreview.className = "w-14 h-14 flex items-center justify-center rounded-lg bg-gray-100 text-gray-500 text-xs";
            filePreview.innerText = "FILE";
        }

        const info = document.createElement('div');
        info.className = "flex-1 ml-4";

        info.innerHTML = `
            <p class="text-sm font-medium truncate">${file.name}</p>
            <p class="text-xs text-gray-400">
                ${(file.size / 1024).toFixed(1)} KB
            </p>
        `;

        const removeBtn = document.createElement('button');
        removeBtn.type = "button";
        removeBtn.className = "text-red-500 text-xs hover:underline";
        removeBtn.innerText = "Remove";
        removeBtn.onclick = () => removeFile(index);

        const left = document.createElement('div');
        left.className = "flex items-center";
        left.appendChild(filePreview);
        left.appendChild(info);

        wrapper.appendChild(left);
        wrapper.appendChild(removeBtn);

        preview.appendChild(wrapper);
    });

    fileInput.files = dataTransfer.files;

    // UPDATE META INFO
    if (selectedFiles.length > 0) {

        const totalBytes = selectedFiles.reduce((sum, file) => sum + file.size, 0);

        const totalKB = (totalBytes / 1024).toFixed(1);
        const totalMB = (totalBytes / (1024 * 1024)).toFixed(2);

        fileMeta.classList.remove('hidden');

        fileCount.innerText = `${selectedFiles.length} file${selectedFiles.length > 1 ? 's' : ''}`;
        fileSize.innerText = totalMB >= 1 ? `${totalMB} MB` : `${totalKB} KB`;

    } else {
        fileMeta.classList.add('hidden');
    }

}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updatePreview();
}
</script>


