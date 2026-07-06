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
                <h2 class="text-xl font-bold">{{ $folder->name }}</h2>
                <div class="text-sm text-gray-500">
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
                <p class="text-sm text-gray-500">{{ $folder->files->count() }} files</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-3">
                <!-- <div class="relative w-full min-w-[260px]">
                    <input x-model.debounce.200ms="searchFiles" type="search" placeholder="Search files by name or date" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-indigo-600 focus:ring-indigo-600" />
                </div> -->
                <button @click="openAddFiles = true" class="bg-[#0f1b3d] text-white px-4 py-2 rounded shadow cursor-pointer">Add</button>
                
            </div>
        </div>

        @if($folder->files->isEmpty())
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                <div class="text-xl font-semibold">No files yet</div>
                <!-- <p class="mt-2">Upload files using the button above.</p> -->
            </div>
        @else
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
                        <div>
                            <div class="font-medium text-gray-900">{{ $file->original_filename }}</div>
                            <div class="text-sm text-gray-500">{{ number_format($file->file_size / 1024, 2) }} KB</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('attendance.files.show', [$folder, $file]) }}" target="_blank" class="text-sm font-medium text-blue-600 transition group-hover:text-blue-800 hover:underline">View</a>
                            <form method="POST" action="{{ route('attendance.files.destroy', [$folder, $file]) }}" onsubmit="return confirm('Delete file?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-medium text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div x-show="openEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div class="absolute inset-0" @click="openEdit = false"></div>
        <div class="relative w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
                <div>
                    <h3 class="text-xl font-semibold">Edit Attendance Folder</h3>
                    <p class="text-sm text-gray-500">Update the folder name or description and save.</p>
                </div>
                <button type="button" @click="openEdit = false" class="text-gray-500 hover:text-gray-900">Close</button>
            </div>

            <form method="POST" action="{{ route('attendance.update', $folder) }}" @submit.prevent="if (!submitting) { submitting = true; $event.target.submit() }" class="space-y-4 px-6 py-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <div class="font-semibold">Please fix the following:</div>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-2 lg:grid-cols-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input name="name" value="{{ old('name', $folder->name) }}" required class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600" />
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Month</label>
                        <select name="start_month" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600">
                            @for ($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}" {{ old('start_month', optional($folder->folder_date)->month ?? now()->month) == $month ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Year</label>
                        <input type="number" name="start_year" value="{{ old('start_year', optional($folder->folder_date)->year ?? now()->year) }}" min="2000" max="2100" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600" />
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Month</label>
                        <select name="end_month" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600">
                            @for ($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}" {{ old('end_month', optional($folder->folder_date_end)->month ?? optional($folder->folder_date)->month ?? now()->month) == $month ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Year</label>
                        <input type="number" name="end_year" value="{{ old('end_year', optional($folder->folder_date_end)->year ?? optional($folder->folder_date)->year ?? now()->year) }}" min="2000" max="2100" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600" />
                    </div>
                </div>

                <div class="grid gap-2 lg:grid-cols-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600">{{ old('description', $folder->description) }}</textarea>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="openEdit = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="submitting" class="rounded-xl bg-[#0f1b3d] px-4 py-2 text-sm font-medium text-white hover:bg-[#111f3b]">Save</button>
                </div>
            </form>
        </div>
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
@endsection
