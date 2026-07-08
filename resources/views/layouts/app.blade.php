@extends('layouts.authenticated')

@section('content')
<div class="flex h-screen bg-gray-100">
    <aside class="w-64 bg-[#0f1b3d] text-white flex flex-col">
        <div class="p-6 border-b border-[#13203a]">
            <div class="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center text-xl font-bold">TC</div>
            <div class="mt-3 font-bold">Toledo City Hall</div>
            <div class="text-sm text-yellow-400">BAC Document System</div>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 p-3 rounded-md hover:bg-[#13203a] {{ request()->routeIs('dashboard') ? 'bg-[#13203a] border-l-4 border-yellow-400' : ''}}"> 
                <span class="w-5">🏠</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 p-3 rounded-md hover:bg-[#13203a] {{ request()->routeIs('attendance.*') ? 'bg-[#13203a] border-l-4 border-yellow-400' : ''}}"> 
                <span class="w-5">📁</span>
                <span>Attendance</span>
            </a>
            <div class="text-slate-300 mt-4 text-xs px-3">Other</div>
            <button class="flex items-center gap-3 p-3 rounded-md opacity-60 cursor-not-allowed">📄 Minutes Documents</button>
            <button class="flex items-center gap-3 p-3 rounded-md opacity-60 cursor-not-allowed">⬆ Upload Minutes</button>
            <button class="flex items-center gap-3 p-3 rounded-md opacity-60 cursor-not-allowed">🔍 Search Documents</button>
            <button class="flex items-center gap-3 p-3 rounded-md opacity-60 cursor-not-allowed">🗄 Archives</button>
            <button class="flex items-center gap-3 p-3 rounded-md opacity-60 cursor-not-allowed">🔔 Notifications</button>
            <button class="flex items-center gap-3 p-3 rounded-md opacity-60 cursor-not-allowed">📜 Activity Logs</button>
            <button class="flex items-center gap-3 p-3 rounded-md opacity-60 cursor-not-allowed">👥 User Management</button>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 p-3 rounded-md hover:bg-[#13203a] {{ request()->routeIs('settings.*') ? 'bg-[#13203a] border-l-4 border-yellow-400' : ''}}">⚙ Settings</a>
            @else
                <button class="flex items-center gap-3 p-3 rounded-md opacity-60 cursor-not-allowed">⚙ Settings</button>
            @endif
        </nav>

        <div class="p-4 border-t border-[#13203a]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-yellow-400 text-[#0f1b3d] flex items-center justify-center font-bold">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                <div>
                    <div class="text-sm">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-slate-300">{{ auth()->user()->position_title }}</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
            <div>
                <div class="text-lg font-semibold">@yield('page-title', 'Dashboard')</div>
                <div class="text-sm text-gray-500">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</div>
            </div>

            <div class="flex items-center gap-4">
                <button class="p-2 rounded hover:bg-gray-100">🔔</button>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 p-2 rounded hover:bg-gray-100">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                        <div class="text-sm">{{ auth()->user()->name }}</div>
                    </button>

                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-44 bg-white rounded shadow-md py-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6 overflow-auto">
            @yield('content')
        </main>
    </div>
</div>

@endsection
