<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BAC Minutes and Attendance Management System') }}</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <script>
        window.addEventListener('pageshow', function (event) {
            const nav = window.performance && window.performance.getEntriesByType('navigation')[0];
            if (event.persisted || (nav && nav.type === 'back_forward')) {
                window.location.reload();
            }
        });
    </script>
    <div class="flex h-screen bg-[radial-gradient(circle_at_top_left,_rgba(15,27,61,0.06),_transparent_35%)]">
        <aside class="flex w-72 flex-col border-r border-slate-200 bg-white/80  shadow-sm backdrop-blur">
            <div class="mb-8 bg-[#0f1b3d] px-4 py-6 text-center text-white shadow-sm">
                <div class="inline-flex items-center text-center rounded-full bg-[#0f1b3d] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-white">
                    Bidding and Awards Committee Office
                </div>
                <!-- <div class="mt-4 text-lg font-semibold text-slate-900">Bidding and Awards Committee</div> -->
                <div class="text-sm text-slate-500 text-center text text-white" >Toledo City Hall</div>
            </div>

            <nav class="flex-1">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-[#0f1b3d] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <span class="mr-3 text-base">▤</span>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('attendance.index') }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('attendance.*') ? 'bg-[#0f1b3d] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <span class="mr-3 text-base">📅</span>
                            Attendance
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('minutes.index') }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('minutes.*') ? 'bg-[#0f1b3d] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <span class="mr-3 text-base">📄</span>
                            Minutes Documents
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition">
                            <span class="mr-3 text-base">🗂️</span>
                            Archives (coming)
                        </a>
                    </li>
                    @if(auth()->user()->isAdmin())
                        <li>
                            <a href="{{ route('settings.index') }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('settings.*') ? 'bg-[#0f1b3d] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="mr-3 text-base">⚙</span>
                                Settings
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>

            <div class="mt-2 p-4">
                <!-- <div class="mb-2 text-sm font-semibold text-slate-700">Need a quick exit?</div> -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-auto p-6 lg:p-8">
            <div class="mx-auto max-w-7xl">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
