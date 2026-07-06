<?php

use App\Models\AttendanceFile;
use App\Models\AttendanceFolder;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $folders = AttendanceFolder::withTrashed()->get();
        $files = AttendanceFile::query();
        $filesThisYear = (clone $files)->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()])->count();
        $filesThisMonth = (clone $files)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $stats = [
            'folders' => $folders->count(),
            'files' => $files->count(),
            'filesThisYear' => $filesThisYear,
            'filesThisMonth' => $filesThisMonth,
            'archivedFolders' => $folders->whereNotNull('deleted_at')->count(),
        ];

        return view('dashboard', compact('stats'));
    })->name('dashboard');
});
