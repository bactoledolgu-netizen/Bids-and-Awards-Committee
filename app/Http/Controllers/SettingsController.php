<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Setting;

class SettingsController extends Controller
{
    protected function authorizeAdmin(): void
    {
        if (! auth()->user() || ! auth()->user()->isAdmin()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->authorizeAdmin();

        $attendancePasswordSet = (bool) Setting::get('attendance_password');

        return view('settings.index', compact('attendancePasswordSet'));
    }

    public function update(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'attendance_password' => 'required|string|min:8|confirmed',
        ]);

        Setting::set('attendance_password', Hash::make($request->input('attendance_password')));

        return redirect()->route('settings.index')->with('success', 'Attendance password updated successfully.');
    }
}
