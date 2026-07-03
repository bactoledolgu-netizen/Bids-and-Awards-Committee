@extends('layouts.app')

@section('page-title', $folder->name)

@section('content')
<div x-data="{ openEdit: {{ $errors->any() ? 'true' : 'false' }}, openAction: null, openAddFolder: false, openAddFiles: false, submitting: false }" @keydown.escape.window="openEdit = false; openAction = null; openAddFolder = false; openAddFiles = false" class="max-w-5xl mx-auto">
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
                <div class="text-sm text-gray-500">{{ optional($folder->folder_date)->format('F j, Y') }}</div>
                <p class="mt-3 text-gray-700">{{ $folder->description }}</p>
                
            </div>
            
        </div>
        
    </div>

    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold">Subfolders</h3>
                <p class="text-sm text-gray-500">{{ $folder->children->count() }} folders</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openAddFolder = true" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add Folder</button>
                <!-- <button @click="openAddFiles = true" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add Files</button> -->
            </div>
        </div>

        @if($folder->children->isEmpty())
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                <div class="text-xl font-semibold">No subfolders yet</div>
                <!-- <p class="mt-2">Add a folder or upload files using the buttons above.</p> -->
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($folder->children as $child)
                    <div x-data="{ menuOpen: false, renameOpen: false, submittingRename: false }" class="group bg-white rounded-3xl shadow p-4 relative border border-transparent transition hover:border-blue-200 hover:shadow-lg">
                        <div class="absolute top-4 right-4">
                            <button @click.stop="menuOpen = !menuOpen" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 hover:bg-gray-50">•••</button>
                            <div x-show="menuOpen" x-cloak @click.outside="menuOpen = false" class="absolute right-0 mt-2 w-36 rounded-2xl border border-gray-200 bg-white shadow-lg overflow-hidden">
                                <button @click="renameOpen = true; menuOpen = false" class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Rename</button>
                                <form method="POST" action="{{ route('attendance.destroy', $child) }}" onsubmit="return confirm('Delete folder?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100">Delete</button>
                                </form>
                            </div>
                        </div>

                        <a href="{{ route('attendance.show', $child) }}" class="block rounded-3xl p-2 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <div class="space-y-2">
                                <div class="font-semibold text-lg text-gray-900">{{ $child->name }}</div>
                                <div class="text-sm text-gray-500">{{ optional($child->folder_date)->format('F j, Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $child->files->count() }} files</div>
                            </div>
                        </a>

                        <div x-show="renameOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
                            <div class="absolute inset-0" @click="renameOpen = false"></div>
                            <div class="relative w-full max-w-xl rounded-3xl bg-white shadow-2xl overflow-hidden">
                                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
                                    <div>
                                        <h3 class="text-xl font-semibold">Rename Folder</h3>
                                        <p class="text-sm text-gray-500">Change the folder title or description.</p>
                                    </div>
                                    <button type="button" @click="renameOpen = false" class="text-gray-500 hover:text-gray-900">Close</button>
                                </div>

                                <form method="POST" action="{{ route('attendance.update', $child) }}" @submit.prevent="if (!submittingRename) { submittingRename = true; $event.target.submit() }" class="space-y-4 px-6 py-6">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name</label>
                                        <input name="name" value="{{ old('name', $child->name) }}" required class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea name="description" rows="3" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600">{{ old('description', $child->description) }}</textarea>
                                    </div>

                                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                                        <button type="button" @click="renameOpen = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                                        <button type="submit" :disabled="submittingRename" class="rounded-xl bg-[#0f1b3d] px-4 py-2 text-sm font-medium text-white hover:bg-[#111f3b]">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold">Files</h3>
                <p class="text-sm text-gray-500">{{ $folder->files->count() }} files</p>
            </div>
            <button @click="openAddFiles = true" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add Files</button>
        </div>

        @if($folder->files->isEmpty())
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                <div class="text-xl font-semibold">No files yet</div>
                <!-- <p class="mt-2">Upload files using the button above.</p> -->
            </div>
        @else
            <div class="space-y-3">
                @foreach($folder->files as $file)
                    <div class="group flex items-center justify-between rounded-3xl border border-gray-200 bg-gray-50 p-4 transition hover:border-blue-200 hover:bg-slate-50">
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

    <div x-show="openAddFolder" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div class="absolute inset-0" @click="openAddFolder = false"></div>
        <div class="relative w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
                <div>
                    <h3 class="text-xl font-semibold">Add Subfolder</h3>
                    <p class="text-sm text-gray-500">Create a nested folder inside this folder.</p>
                </div>
                <button type="button" @click="openAddFolder = false" class="text-gray-500 hover:text-gray-900">Close</button>
            </div>

            <form method="POST" action="{{ route('attendance.store') }}" @submit.prevent="if (!submitting) { submitting = true; $event.target.submit() }" class="space-y-4 px-6 py-6">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $folder->id }}" />

                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="3" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600">{{ old('description') }}</textarea>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="openAddFolder = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="submitting" class="rounded-xl bg-[#0f1b3d] px-4 py-2 text-sm font-medium text-white hover:bg-[#111f3b]">Create Folder</button>
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
                    <input type="file" name="files[]" multiple required class="mt-1 block w-full" />
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
