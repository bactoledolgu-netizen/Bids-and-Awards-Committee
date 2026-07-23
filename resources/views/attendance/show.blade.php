@extends('layouts.app')

@section('page-title', $folder->name)

@section('content')
@php
    $oldFileId = old('file_id');
    $oldVerifyUrl = $oldFileId ? $folder->files->firstWhere('id', $oldFileId) : null;
    $uploadErrors = array_merge($errors->get('files'), $errors->get('files.*'));
@endphp

<div x-data="{ openEdit: {{ $errors->any() ? 'true' : 'false' }}, openAction: null, openAddFolder: false, openAddFiles: {{ count($uploadErrors) > 0 ? 'true' : 'false' }}, openPasswordModal: {{ $oldFileId ? 'true' : 'false' }}, openDeleteModal: false, uploading: false, uploadProgress: 0, uploadStatus: 'Preparing your files...', selectedFiles: [], passwordVerifyUrl: {{ $oldVerifyUrl ? json_encode(route('attendance.files.verify', [$folder, $oldVerifyUrl])) : 'null' }}, submitting: false, draggingId: null, reorderUrl: '{{ route('attendance.files.reorder', $folder) }}' }" @upload-start.window="uploading = true; uploadProgress = 0; uploadStatus = 'Preparing your files...'" @upload-progress.window="uploadProgress = $event.detail.percent; uploadStatus = $event.detail.percent < 100 ? 'Uploading your files...' : 'Finishing up...'" @keydown.escape.window="if (!uploading) { openEdit = false; openAction = null; openAddFolder = false; openAddFiles = false; openPasswordModal = false; openDeleteModal = false }" class="max-w-5xl mx-auto">
    <div class="mb-4">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('attendance.index') }}" class="text-blue-600 hover:underline">Attendance</a>
            @foreach ($ancestors as $ancestor)
                <span>/</span>
                <a href="{{ route('attendance.show', $ancestor) }}" class="text-blue-600 hover:underline">{{ $ancestor->name }}</a>
            @endforeach
            <span>/</span>
            <span class="text-gray-900">
                    @if($folder->folder_date_end)
                        {{ $folder->folder_date->format('F Y') }} - {{ $folder->folder_date_end->format('F Y') }}
                    @else
                        {{ optional($folder->folder_date)->format('F j, Y') }}
                    @endif</span>
                    
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
            <form id="file-search-form" method="GET" action="{{ route('attendance.show', $folder) }}" class="relative min-w-[280px]">
                <!-- <label for="attendance-file-search" class="sr-only">Search files</label> -->
                <input id="attendance-file-search" type="search" name="file_search" value="{{ $fileSearch }}" placeholder="Search files..." autocomplete="off" class="w-full rounded-xl border border-gray-300 px-3 py-2 pr-14 text-sm focus:border-indigo-600 focus:ring-indigo-600" />
                <!-- <button id="file-search-clear" type="button" class="absolute inset-y-0 right-3 hidden text-sm font-medium text-blue-600 hover:text-blue-800">Clear</button> -->
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold">Files</h3>
                <p class="text-sm text-gray-500">{{ $files->count() }} files · {{ number_format($files->sum('file_size') / 1024 / 1024, 2) }} MB total</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-3">
                <button @click="openAddFiles = true" class="bg-[#0f1b3d] text-white px-4 py-2 rounded shadow cursor-pointer">Add</button>
            </div>
        </div>

        @if($files->isEmpty())
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                <div class="text-xl font-semibold">{{ $fileSearch !== '' ? 'No matching files' : 'No files yet' }}</div>
                <!-- <p class="mt-2">Upload files using the button above.</p> -->
            </div>
        @else
            @if(session('warning'))
                <div class="mb-4 rounded-2xl bg-yellow-50 border border-yellow-200 p-4 text-yellow-800">
                    {{ session('warning') }}
                </div>
            @endif
            @if($errors->has('attendance_password'))
                <div class="mb-4 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-800">
                    {{ $errors->first('attendance_password') }}
                </div>
            @endif
            <form id="bulk-delete-form" method="POST" action="{{ route('attendance.files.bulk-destroy', $folder) }}" class="space-y-3">
                @csrf
                @method('DELETE')
                <div class="mb-3 flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" id="select-all-files" class="rounded border-gray-300 text-[#0f1b3d] focus:ring-[#0f1b3d]">
                        <span>Select all</span>
                    </label>
                    <button type="button" id="bulk-delete-button" @click="if (selectedFiles.length) openDeleteModal = true" disabled class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50">Delete selected</button>
                </div>
                <div class="space-y-3" id="file-list">
                    @foreach($files as $file)
                        <div
                            draggable="true"
                            data-file-id="{{ $file->id }}"
                            data-file-name="{{ strtolower($file->original_filename) }}"
                            class="group flex cursor-move items-center justify-between rounded-3xl border border-gray-200 bg-gray-50 p-4 transition hover:border-blue-200 hover:bg-slate-50"
                        >
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="file_ids[]" value="{{ $file->id }}" data-file-name="{{ $file->original_filename }}" @change="selectedFiles = Array.from(document.querySelectorAll('.file-checkbox:checked')).map(checkbox => checkbox.dataset.fileName)" class="file-checkbox rounded border-gray-300 text-[#0f1b3d] focus:ring-[#0f1b3d]">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $file->original_filename }}</div>
                                    <div class="text-sm text-gray-500">{{ number_format($file->file_size / 1024, 2) }} KB</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="passwordVerifyUrl = '{{ route('attendance.files.verify', [$folder, $file]) }}'; openPasswordModal = true" class="rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-50">View</button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div id="no-search-results" class="hidden rounded-2xl border border-dashed border-gray-300 p-8 text-center text-gray-500">No matching files</div>
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

            <form action="{{ route('attendance.files.store', $folder) }}" method="POST" enctype="multipart/form-data" class="file-upload-form space-y-4 px-6 py-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Choose files</label>
                    @if(count($uploadErrors) > 0)
                        <div class="mt-3 rounded-xl bg-red-50 p-3 text-sm text-red-700">
                            @foreach($uploadErrors as $uploadError)
                                <div>{{ $uploadError }}</div>
                            @endforeach
                        </div>
                    @endif
                    <input type="file" name="files[]" multiple required class="mt-3 block w-full cursor-pointer bg-gray-100 border border-gray-300 focus:border-indigo-600 focus:ring-indigo-600" />
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="openAddFiles = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="submitting" class="rounded-xl bg-[#0f1b3d] px-4 py-2 text-sm font-medium text-white hover:bg-[#111f3b]">Upload Files</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="openPasswordModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div class="absolute inset-0" @click="openPasswordModal = false"></div>
        <div class="relative w-full max-w-md rounded-3xl bg-white shadow-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-xl font-semibold">Enter Attendance Password</h3>
                <p class="text-sm text-gray-500">This file is protected. Please enter the global attendance password to continue.</p>
            </div>
            <form :action="passwordVerifyUrl" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Attendance Password</label>
                    <input type="password" name="attendance_password" required class="mt-3 block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 focus:border-[#0f1b3d] focus:outline-none focus:ring-2 focus:ring-[#0f1b3d]/20" />
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="openPasswordModal = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="rounded-xl bg-[#0f1b3d] px-4 py-2 text-sm font-medium text-white hover:bg-[#111f3b]">Verify</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="openDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div class="absolute inset-0" @click="openDeleteModal = false"></div>
        <div class="relative w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-5">
                <h3 class="text-xl font-semibold">Delete selected files?</h3>
                <p class="mt-1 text-sm text-gray-500">The following files will be permanently deleted.</p>
            </div>
            <div class="max-h-64 overflow-y-auto px-6 py-5">
                <ul class="list-disc space-y-2 pl-5 text-sm text-gray-700">
                    <template x-for="fileName in selectedFiles" :key="fileName">
                        <li class="break-words" x-text="fileName"></li>
                    </template>
                </ul>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
                <button type="button" @click="openDeleteModal = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="button" @click="document.getElementById('bulk-delete-form').submit()" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Delete files</button>
            </div>
        </div>
    </div>

    <div x-show="uploading" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-[#0f1b3d]/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-3xl bg-white p-8 text-center shadow-2xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-[#0f1b3d]">
                <svg class="h-8 w-8 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16V4m0 0L7 9m5-5 5 5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3" stroke-linecap="round"/></svg>
            </div>
            <h3 class="mt-5 text-xl font-semibold text-slate-900">Uploading files</h3>
            <p class="mt-2 text-sm text-slate-500" x-text="uploadStatus"></p>
            <div class="mt-6 h-3 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-[#0f1b3d] transition-[width] duration-200" :style="`width: ${uploadProgress}%`"></div>
            </div>
            <div class="mt-3 text-3xl font-bold text-[#0f1b3d]" x-text="`${uploadProgress}%`"></div>
            <p class="mt-2 text-xs text-slate-400">Please keep this window open while the upload completes.</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.file-upload-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (form.dataset.uploading === 'true') {
                    return;
                }
                form.dataset.uploading = 'true';
                window.dispatchEvent(new CustomEvent('upload-start'));
                const request = new XMLHttpRequest();
                request.open(form.method || 'POST', form.action);
                request.upload.addEventListener('progress', function (progressEvent) {
                    if (progressEvent.lengthComputable) {
                        window.dispatchEvent(new CustomEvent('upload-progress', { detail: { percent: Math.round((progressEvent.loaded / progressEvent.total) * 100) } }));
                    }
                });
                request.addEventListener('load', function () {
                    window.location.assign(request.responseURL || form.action);
                });
                request.addEventListener('error', function () {
                    form.dataset.uploading = 'false';
                    window.dispatchEvent(new CustomEvent('upload-progress', { detail: { percent: 0 } }));
                    alert('The upload failed. Please check your connection and try again.');
                });
                request.send(new FormData(form));
            });
        });

        const fileList = document.getElementById('file-list');
        const searchForm = document.getElementById('file-search-form');
        const searchInput = document.getElementById('attendance-file-search');
        const clearSearch = document.getElementById('file-search-clear');
        const noSearchResults = document.getElementById('no-search-results');
        const allFileItems = fileList ? Array.from(fileList.children) : [];
        const filterFiles = function () {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;
            allFileItems.forEach(function (item) { const visible = !query || item.dataset.fileName.includes(query); item.classList.toggle('hidden', !visible); if (visible) visibleCount++; });
            if (clearSearch) clearSearch.classList.toggle('hidden', query === '');
            if (noSearchResults) noSearchResults.classList.toggle('hidden', allFileItems.some(function (item) { return !item.classList.contains('hidden'); }) || query === '');
        };
        searchInput.addEventListener('input', filterFiles);
        searchForm.addEventListener('submit', function (event) { event.preventDefault(); filterFiles(); });
        if (clearSearch) {
            clearSearch.addEventListener('click', function () { searchInput.value = ''; filterFiles(); searchInput.focus(); });
        }
        filterFiles();
        const scrollContainer = document.querySelector('main');
        let draggedFile = null;

        if (fileList) {
            fileList.querySelectorAll('[draggable="true"]').forEach(function (fileItem) {
                fileItem.addEventListener('dragstart', function () {
                    draggedFile = fileItem;
                    fileItem.classList.add('opacity-50');
                    document.body.classList.add('file-dragging');
                });

                fileItem.addEventListener('dragover', function (event) {
                    event.preventDefault();
                });

                fileItem.addEventListener('drop', function (event) {
                    event.preventDefault();
                    if (!draggedFile || draggedFile === fileItem) {
                        return;
                    }

                    const items = Array.from(fileList.children);
                    const fromIndex = items.indexOf(draggedFile);
                    const toIndex = items.indexOf(fileItem);
                    if (fromIndex > -1 && toIndex > -1) {
                        items.splice(fromIndex, 1);
                        items.splice(toIndex, 0, draggedFile);
                        fileList.replaceChildren(...items);
                        fetch('{{ route('attendance.files.reorder', $folder) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                            },
                            body: JSON.stringify({ order: items.map(function (item) { return item.dataset.fileId; }) })
                        });
                    }
                });

                fileItem.addEventListener('dragend', function () {
                    fileItem.classList.remove('opacity-50');
                    draggedFile = null;
                    document.body.classList.remove('file-dragging');
                });
            });
        }

        document.addEventListener('dragover', function (event) {
            if (!document.body.classList.contains('file-dragging')) {
                return;
            }

            if (!scrollContainer) {
                return;
            }

            const bounds = scrollContainer.getBoundingClientRect();
            const edgeDistance = 90;
            const scrollSpeed = 18;

            if (event.clientY < bounds.top + edgeDistance) {
                scrollContainer.scrollTop -= scrollSpeed;
            } else if (event.clientY > bounds.bottom - edgeDistance) {
                scrollContainer.scrollTop += scrollSpeed;
            }
        });

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
                checkboxes.forEach(function (checkbox) {
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
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
