<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceFolderController;
use App\Http\Controllers\AttendanceFileController;

Route::middleware(['auth'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceFolderController::class, 'index'])->name('index');
    Route::get('/create', [AttendanceFolderController::class, 'create'])->name('create');
    Route::post('/', [AttendanceFolderController::class, 'store'])->name('store');
    Route::get('/{folder}', [AttendanceFolderController::class, 'show'])->name('show');
    Route::get('/{folder}/edit', [AttendanceFolderController::class, 'edit'])->name('edit');
    Route::put('/{folder}', [AttendanceFolderController::class, 'update'])->name('update');
    Route::delete('/{folder}', [AttendanceFolderController::class, 'destroy'])->name('destroy');

    Route::post('/{folder}/files', [AttendanceFileController::class, 'store'])->name('files.store');
    Route::get('/{folder}/files/{file}', [AttendanceFileController::class, 'show'])->name('files.show');
    Route::delete('/{folder}/files/{file}', [AttendanceFileController::class, 'destroy'])->name('files.destroy');
});
