<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Guests should be shown the login page; authenticated users go to the welcome landing.
    if (auth()->check()) {
        return redirect()->route('welcome');
    }

    return redirect()->route('login');
});

// Welcome landing for authenticated users — shows the dashboard by redirecting there.
Route::get('/welcome', function () {
    return view('dashboard');
})->middleware('auth')->name('welcome');

require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/attendance.php';
