<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('welcome');
    }

    return redirect()->route('login');
})->middleware('web');

// Welcome landing for authenticated users — shows the dashboard by redirecting there.
Route::get('/welcome', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'web'])->name('welcome');

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/attendance.php';
require __DIR__.'/minutes.php';
