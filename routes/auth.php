<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;

Route::middleware(['guest', 'web'])->group(function () {
    Route::get('login', function () {
        if (auth()->check()) {
            return redirect()->route('welcome');
        }

        return response()->view('auth.login')->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    })->name('login');

    Route::post('login', function (LoginRequest $request) {
        $request->authenticate();
        $request->session()->regenerate();
        return redirect()->route('welcome');
    });
});

Route::post('logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return response()->redirectTo('/')->withHeaders([
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
    ]);
})->middleware(['auth', 'web'])->name('logout');
