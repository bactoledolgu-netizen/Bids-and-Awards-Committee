<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BAC Minutes and Attendance Management System') }}</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 p-4 flex flex-col">
            <div class="mb-6">
                <div class="text-lg font-semibold">BAC Minutes & Attendance</div>
                <div class="text-xs text-gray-500">Toledo City Hall</div>
            </div>

            <!-- <div class="mb-6">
                <div class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                <div class="mt-2 text-sm font-medium">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gray-500">{{ auth()->user()->position_title ?? 'Administrator' }}</div>
                <div class="text-xs text-gray-400">{{ '@' . auth()->user()->username }}</div>
            </div> -->

            <nav class="flex-1">
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">Dashboard</a>
                    </li>
                    <li>
                        <a href="{{ route('attendance.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">Attendance</a>
                    </li>
                    <li>
                        <a href="#" class="block px-3 py-2 rounded text-gray-400">Documents (coming)</a>
                    </li>
                    <li>
                        <a href="#" class="block px-3 py-2 rounded text-gray-400">Archives (coming)</a>
                    </li>
                </ul>
            </nav>

            <div class="mt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 text-white px-3 py-2 rounded">Logout</button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 overflow-auto p-6">
            @yield('content')
        </div>
    </div>
</body>
</html>
