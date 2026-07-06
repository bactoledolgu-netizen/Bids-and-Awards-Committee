<?php

use Illuminate\Support\Facades\Route;

Route::get('/minutes', function () {
    return view('minutes.index');
})->name('minutes.index');

