@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-3xl shadow p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold">Settings</h1>
                <p class="text-sm text-gray-500">Manage the global attendance file view password.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-2xl bg-green-50 border border-green-200 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Attendance Password</label>
                <input type="password" name="attendance_password" autocomplete="new-password" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 focus:border-[#0f1b3d] focus:outline-none focus:ring-2 focus:ring-[#0f1b3d]/20" placeholder="Enter new attendance password" required>
                @error('attendance_password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="attendance_password_confirmation" autocomplete="new-password" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 focus:border-[#0f1b3d] focus:outline-none focus:ring-2 focus:ring-[#0f1b3d]/20" placeholder="Confirm password" required>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                <!-- <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Logout</button>
                </form> -->
                <button type="submit" class="w-full sm:w-auto rounded-2xl bg-[#0f1b3d] px-6 py-3 text-sm font-semibold text-white hover:bg-[#111f3b]">Save Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
