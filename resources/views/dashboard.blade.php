@extends('layouts.app')

@section('page-title','Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Welcome banner -->
    <div class="bg-[#0f2a57] text-white rounded-xl p-6 flex items-center gap-6">
        <!-- <div class="w-20 h-20 rounded-full bg-white/10 flex items-center justify-center text-2xl font-bold">TC</div> -->
        <div class="flex-1">
            <!-- <div class="text-sm uppercase text-yellow-300">Welcome back</div> -->
            <div class="text-2xl font-semibold">{{ auth()->user()->name }}</div>
            <div class="text-sm text-white/80 mt-1">{{ auth()->user()->position_title ?? 'System Administrator' }} · BAC Chairperson</div>
        </div>
        <div class="text-right">
            <div class="text-sm text-white/80">Today</div>
            <div class="text-lg font-semibold">{{ \Carbon\Carbon::now()->format('F j, Y') }}</div>
        </div>
    </div>

    <!-- Stats cards -->
    @php($stats = $stats ?? [
        'folders' => 0,
        'filesThisYear' => 0,
        'filesThisMonth' => 0,
        'files' => 0,
        'archivedFolders' => 0,
    ])
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-gray-400">Total Folders</div>
            <div class="text-2xl font-bold mt-2">{{ $stats['folders'] }}</div>
            <div class="text-xs text-gray-400 mt-1">All attendance folders</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-gray-400">Uploaded This Year</div>
            <div class="text-2xl font-bold mt-2">{{ $stats['filesThisYear'] }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ now()->format('Y') }} — Jan to {{ now()->format('M') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-gray-400">This Month</div>
            <div class="text-2xl font-bold mt-2">{{ $stats['filesThisMonth'] }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ now()->format('F Y') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-gray-400">Total Files</div>
            <div class="text-2xl font-bold mt-2">{{ $stats['files'] }}</div>
            <div class="text-xs text-gray-400 mt-1">Across all folders</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-gray-400">Archived Folders</div>
            <div class="text-2xl font-bold mt-2">{{ $stats['archivedFolders'] }}</div>
            <div class="text-xs text-gray-400 mt-1">Soft-deleted folders</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-gray-400">Storage Used</div>
            <div class="text-2xl font-bold mt-2">{{ number_format($stats['files'] > 0 ? ($stats['files'] * 0.5) : 0, 1) }} MB</div>
            <div class="text-xs text-gray-400 mt-1">Based on current file count</div>
        </div>
    </div>

    <!-- Main columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="font-semibold">Monthly Upload Statistics</div>
                    <div class="text-sm text-gray-500">2026 — Minutes uploaded per month</div>
                </div>
                <!-- Simple bar chart placeholder -->
                <div class="h-48">
                    <svg viewBox="0 0 600 200" class="w-full h-full">
                        <!-- grid lines -->
                        <g stroke="#e6eef8" stroke-width="1">
                            <line x1="0" y1="20" x2="600" y2="20" />
                            <line x1="0" y1="60" x2="600" y2="60" />
                            <line x1="0" y1="100" x2="600" y2="100" />
                            <line x1="0" y1="140" x2="600" y2="140" />
                            <line x1="0" y1="180" x2="600" y2="180" />
                        </g>
                        <!-- bars -->
                        <g fill="#0f2a57">
                            <rect x="40" y="100" width="28" height="80" rx="6" />
                            <rect x="110" y="60" width="28" height="120" rx="6" />
                            <rect x="180" y="80" width="28" height="100" rx="6" />
                            <rect x="250" y="40" width="28" height="140" rx="6" />
                            <rect x="320" y="70" width="28" height="110" rx="6" />
                            <rect x="390" y="20" width="28" height="160" rx="6" />
                            <rect x="460" y="120" width="28" height="60" rx="6" />
                        </g>
                        <!-- months labels -->
                        <g fill="#6b7280" font-size="12">
                            <text x="44" y="196">Jan</text>
                            <text x="114" y="196">Feb</text>
                            <text x="184" y="196">Mar</text>
                            <text x="254" y="196">Apr</text>
                            <text x="324" y="196">May</text>
                            <text x="394" y="196">Jun</text>
                            <text x="464" y="196">Jul</text>
                        </g>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <div class="text-sm text-gray-400">Storage Usage</div>
                        <div class="text-xl font-bold mt-2">2.4 GB</div>
                        <div class="text-xs text-gray-400 mt-1">of 10 GB</div>
                        <div class="mt-3 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-2 bg-yellow-400 rounded-full" style="width:24%"></div>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-sm text-gray-400">Recent Activities</div>
                        <ul class="mt-3 space-y-3">
                            <li class="flex items-start justify-between">
                                <div>
                                    <div class="font-medium">Uploaded Document — BAC-2026-005</div>
                                    <div class="text-xs text-gray-500">Santos · 09:12</div>
                                </div>
                                <div class="text-sm text-gray-400">09:12</div>
                            </li>
                            <li class="flex items-start justify-between">
                                <div>
                                    <div class="font-medium">Viewed Document — BAC-2026-003</div>
                                    <div class="text-xs text-gray-500">Cruz · 09:05</div>
                                </div>
                                <div class="text-sm text-gray-400">09:05</div>
                            </li>
                            <li class="flex items-start justify-between">
                                <div>
                                    <div class="font-medium">Edited Document — BAC-2026-004</div>
                                    <div class="text-xs text-gray-500">Torres · 04:48</div>
                                </div>
                                <div class="text-sm text-gray-400">04:48</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-400">By Category</div>
                        <div class="text-lg font-bold mt-2">All documents</div>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-center">
                    <!-- donut placeholder -->
                    <svg width="160" height="160" viewBox="0 0 36 36" class="inline-block">
                        <path d="M18 2a16 16 0 1 0 0 32a16 16 0 1 0 0-32" fill="#f3f4f6"></path>
                        <path d="M18 2a16 16 0 0 1 12 26" fill="#0f2a57"></path>
                        <circle cx="18" cy="18" r="6" fill="#fff"></circle>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <div class="text-sm text-gray-500">Quick Actions</div>
                <div class="mt-4 space-y-3">
                    <a href="#" class="block bg-[#0f2a57] text-white px-4 py-2 rounded">Upload Minutes</a>
                    <a href="{{ route('attendance.index') }}" class="block bg-green-600 text-white px-4 py-2 rounded">Search Documents</a>
                    <a href="#" class="block bg-purple-600 text-white px-4 py-2 rounded">View Archives</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
