<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
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
    return redirect('/');
})->middleware('auth')->name('logout');
