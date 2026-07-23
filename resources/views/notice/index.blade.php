@extends('layouts.app')

@section('page-title','Notice')

@section('content')
<div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }}, submitting: false, search: '' }" @keydown.escape.window="open = false" class="max-w-6xl mx-auto">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <h2 class="text-2xl font-bold">Notice</h2>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-3">
            <div class="relative min-w-[280px]">
                <input x-model.debounce.200ms="search" type="search" placeholder="Search folders by name or date" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-indigo-600 focus:ring-indigo-600" />
                <!-- <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">🔎</span> -->
            </div>
            <button @click.prevent="open = true" class="bg-[#0f1b3d] text-white px-4 py-2 rounded shadow cursor-pointer">New Folder</button>
        </div>
    </div>

    <div class="space-y-8">
        @forelse ($groupedFolders as $year => $yearFolders)
            <section>
                <div class="mb-3 flex items-center gap-3">
                    <h3 class="text-lg font-semibold text-slate-800">Year {{ $year }}</h3>
                    <!-- <span class="rounded-full py-1 text-xs font-medium text-slate-600 ">{{ $yearFolders->count() }} folders</span> -->
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($yearFolders as $folder)
                        <div x-data="{ menuOpen: false, renameOpen: false, submittingRename: false, searchText: @js($folder->name . ' ' . ($folder->folder_date_end ? $folder->folder_date->format('F Y') . ' ' . $folder->folder_date_end->format('F Y') : optional($folder->folder_date)->format('F j, Y'))) }" x-show="search === '' || searchText.toLowerCase().includes(search.toLowerCase())" class="group bg-white rounded-3xl shadow p-4 relative border border-transparent transition hover:border-blue-200 hover:shadow-lg">
                            <div class="absolute top-4 right-4">
                                <button @click="menuOpen = !menuOpen" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 hover:bg-gray-50">
                                    •••
                                </button>
                                <div x-show="menuOpen" x-cloak @click.outside="menuOpen = false" class="absolute right-0 mt-2 w-36 rounded-2xl border border-gray-200 bg-white shadow-lg overflow-hidden">
                                    <button @click="renameOpen = true; menuOpen = false" class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Rename</button>
                                    <form method="POST" action="{{ route('notice.destroy', $folder) }}" onsubmit="return confirm('Delete folder?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100">Delete</button>
                                    </form>
                                </div>
                            </div>

                            <a href="{{ route('notice.show', $folder) }}" class="block cursor-pointer rounded-3xl p-2 transition hover:bg-gray-50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="text-sm text-gray-500">{{ $folder->name }}</div>
                                        <div class="font-bold text-lg text-gray-900">
                                            @if($folder->folder_date_end)
                                                {{ $folder->folder_date->format('F Y') }} - {{ $folder->folder_date_end->format('F Y') }}
                                            @else
                                                {{ optional($folder->folder_date)->format('F j, Y') }}
                                            @endif
                                        </div>
                                        <div class="text-sm mt-2 text-gray-600">{{ Str::limit($folder->description, 120) }}</div>
                                    </div>
                                </div>
                            </a>

                            <div x-show="renameOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
                                <div class="absolute inset-0" @click="renameOpen = false"></div>
                                <div class="relative w-full max-w-xl rounded-3xl bg-white shadow-2xl overflow-hidden">
                                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
                                        <div>
                                            <h3 class="text-xl font-semibold">Rename Folder</h3>
                                            <!-- <p class="text-sm text-gray-500">Change the folder title or description.</p> -->
                                        </div>
                                        <button type="button" @click="renameOpen = false" class="text-gray-500 hover:text-gray-900">Close</button>
                                    </div>

                                    <form method="POST" action="{{ route('notice.update', $folder) }}" @submit.prevent="if (!submittingRename) { submittingRename = true; $event.target.submit() }" class="space-y-4 px-6 py-6">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Name</label>
                                            <input name="name" value="{{ old('name', $folder->name) }}" required class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600" />
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

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Description</label>
                                            <textarea name="description" rows="3" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600">{{ old('description', $folder->description) }}</textarea>
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
            </section>
        @empty
            <div class="bg-white rounded-xl shadow p-8 text-center">
                <div class="text-lg font-semibold">No folders yet</div>
                <p class="text-sm text-gray-500 mt-2">Create your first notice folder to upload files.</p>
                <button @click.prevent="open = true" class="mt-4 inline-block bg-[#0f1b3d] text-white px-4 py-2 rounded">Create your first folder</button>
            </div>
        @endforelse
    </div>

    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        style="display: none;"
    >
        <div class="absolute inset-0" @click="open = false"></div>
        <div class="relative w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
                <div>
                    <h3 class="text-xl font-semibold">Create Notice Folder</h3>
                    <!-- <p class="text-sm text-gray-500">Enter folder details and save once to avoid duplicates.</p> -->
                </div>
                <button type="button" @click="open = false" class="text-gray-500 hover:text-gray-900">Close</button>
            </div>

            <form
                method="POST"
                action="{{ route('notice.store') }}"
                @submit.prevent="if (!submitting) { submitting = true; $event.target.submit() }"
                class="space-y-4 px-6 py-6"
            >
                @csrf

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
                        <input name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600" />
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Month</label>
                        <select name="start_month" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600">
                            @for ($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}" {{ old('start_month') == $month ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Year</label>
                        <input type="number" name="start_year" value="{{ old('start_year', now()->year) }}" min="2000" max="2100" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600" />
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Month</label>
                        <select name="end_month" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600">
                            @for ($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}" {{ old('end_month') == $month ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Year</label>
                        <input type="number" name="end_year" value="{{ old('end_year', now()->year) }}" min="2000" max="2100" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600" />
                    </div>
                </div>

                <div class="grid gap-2 lg:grid-cols-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-indigo-600 focus:ring-indigo-600">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="open = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'cursor-not-allowed opacity-60' : 'bg-[#0f1b3d] text-white hover:bg-[#111f3b]'"
                        class="rounded-xl px-4 py-2 text-sm font-medium"
                    >
                        <span x-text="submitting ? 'Saving...' : 'Save'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
