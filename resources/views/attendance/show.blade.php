@extends('layouts.app')

@section('page-title', $folder->name)

@section('content')
<div x-data="{ openEdit: {{ $errors->any() ? 'true' : 'false' }}, openAction: null, openAddFolder: false, openAddFiles: false, submitting: false, searchFolders: '', searchFiles: '', draggingId: null, reorderUrl: '{{ route('attendance.files.reorder', $folder) }}' }" @keydown.escape.window="openEdit = false; openAction = null; openAddFolder = false; openAddFiles = false" class="max-w-5xl mx-auto">
    <div class="mb-4">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('attendance.index') }}" class="text-blue-600 hover:underline">Attendance</a>
            @foreach ($ancestors as $ancestor)
                <span>/</span>
                <a href="{{ route('attendance.show', $ancestor) }}" class="text-blue-600 hover:underline">{{ $ancestor->name }}</a>
            @endforeach
            <span>/</span>
            <span class="text-gray-900">{{ $folder->name }}</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-sm text-gray-500">{{ $folder->name }}</h2>
                <div class="text-xl font-bold">
                    @if($folder->folder_date_end)
                        {{ $folder->folder_date->format('F Y') }} - {{ $folder->folder_date_end->format('F Y') }}
                    @else
                        {{ optional($folder->folder_date)->format('F j, Y') }}
                    @endif
                </div>
                <p class="mt-3 text-gray-700">{{ $folder->description }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold">Files</h3>
                <p class="text-sm text-gray-500">{{ $folder->files->count() }} files · {{ number_format($folder->files->sum('file_size') / 1024 / 1024, 2) }} MB total</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-3">
                <button @click="openAddFiles = true" class="bg-[#0f1b3d] text-white px-4 py-2 rounded shadow cursor-pointer">Add</button>
            </div>
        </div>

        @if($folder->files->isEmpty())
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                <div class="text-xl font-semibold">No files yet</div>
                <!-- <p class="mt-2">Upload files using the button above.</p> -->
            </div>
        @else
            <form method="POST" action="{{ route('attendance.files.bulk-destroy', $folder) }}" onsubmit="return confirm('Delete selected files?')" class="space-y-3">
                @csrf
                @method('DELETE')
                <div class="mb-3 flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" id="select-all-files" class="rounded border-gray-300 text-[#0f1b3d] focus:ring-[#0f1b3d]">
                        <span>Select all</span>
                    </label>
                    <button type="submit" id="bulk-delete-button" disabled class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50">Delete selected</button>
                </div>
                <div class="space-y-3" id="file-list">
                    @foreach($folder->files()->orderBy('sort_order')->orderBy('id')->get() as $file)
                        <div
                            x-data="{ searchText: @js($file->original_filename . ' ' . $file->created_at->format('F j, Y')) }"
                            x-show="searchFiles === '' || searchText.toLowerCase().includes(searchFiles.toLowerCase())"
                            draggable="true"
                            @dragstart="draggingId = {{ $file->id }}"
                            @dragover.prevent
                            @drop.prevent="if (draggingId !== null && draggingId !== {{ $file->id }}) { const list = $event.currentTarget.parentElement; const items = Array.from(list.children); const fromIndex = items.findIndex(el => el.getAttribute('data-file-id') == draggingId); const toIndex = items.findIndex(el => el.getAttribute('data-file-id') == {{ $file->id }}); if (fromIndex > -1 && toIndex > -1) { const [moved] = items.splice(fromIndex, 1); items.splice(toIndex, 0, moved); list.replaceChildren(...items); const order = items.map(el => el.getAttribute('data-file-id')); fetch(reorderUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content') }, body: JSON.stringify({ order }) }); } draggingId = null }"
                            data-file-id="{{ $file->id }}"
                            class="group flex cursor-move items-center justify-between rounded-3xl border border-gray-200 bg-gray-50 p-4 transition hover:border-blue-200 hover:bg-slate-50"
                        >
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="file_ids[]" value="{{ $file->id }}" class="file-checkbox rounded border-gray-300 text-[#0f1b3d] focus:ring-[#0f1b3d]">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $file->original_filename }}</div>
                                    <div class="text-sm text-gray-500">{{ number_format($file->file_size / 1024, 2) }} KB</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('attendance.files.show', [$folder, $file]) }}" target="_blank" class="rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-50">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        @endif
    </div>

    <div x-show="openAddFiles" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div class="absolute inset-0" @click="openAddFiles = false"></div>
        <div class="relative w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
                <div>
                    <h3 class="text-xl font-semibold">Upload Files</h3>
                    <p class="text-sm text-gray-500">Add files to this folder for BAC attendance records.</p>
                </div>
                <button type="button" @click="openAddFiles = false" class="text-gray-500 hover:text-gray-900">Close</button>
            </div>

            <form action="{{ route('attendance.files.store', $folder) }}" method="POST" enctype="multipart/form-data" @submit.prevent="if (!submitting) { submitting = true; $event.target.submit() }" class="space-y-4 px-6 py-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Choose files</label>
                    <input type="file" name="files[]" multiple required class="mt-3 block w-full cursor-pointer bg-gray-100 border border-gray-300 focus:border-indigo-600 focus:ring-indigo-600" />
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="openAddFiles = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="submitting" class="rounded-xl bg-[#0f1b3d] px-4 py-2 text-sm font-medium text-white hover:bg-[#111f3b]">Upload Files</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all-files');
        const bulkDeleteButton = document.getElementById('bulk-delete-button');
        const checkboxes = Array.from(document.querySelectorAll('.file-checkbox'));

        const updateBulkDeleteState = function () {
            const hasSelection = checkboxes.some(function (checkbox) {
                return checkbox.checked;
            });

            if (bulkDeleteButton) {
                bulkDeleteButton.disabled = !hasSelection;
            }
        };

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
                updateBulkDeleteState();
            });
        }

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if (!checkbox.checked && selectAll) {
                    selectAll.checked = false;
                }

                if (selectAll && checkboxes.length > 0 && checkboxes.every(function (item) { return item.checked; })) {
                    selectAll.checked = true;
                }

                updateBulkDeleteState();
            });
        });

        updateBulkDeleteState();
    });
</script>
@endsection
