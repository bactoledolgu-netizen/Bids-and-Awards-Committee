<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MinutesFolderController;
use App\Http\Controllers\MinutesFileController;

Route::middleware(['auth'])->prefix('minutes')->name('minutes.')->group(function () {
    Route::get('/', [MinutesFolderController::class, 'index'])->name('index');
    Route::get('/create', [MinutesFolderController::class, 'create'])->name('create');
    Route::post('/', [MinutesFolderController::class, 'store'])->name('store');
    Route::get('/{folder}', [MinutesFolderController::class, 'show'])->name('show');
    Route::get('/{folder}/edit', [MinutesFolderController::class, 'edit'])->name('edit');
    Route::put('/{folder}', [MinutesFolderController::class, 'update'])->name('update');
    Route::delete('/{folder}', [MinutesFolderController::class, 'destroy'])->name('destroy');

    Route::post('/{folder}/files', [MinutesFileController::class, 'store'])->name('files.store');
    Route::post('/{folder}/files/reorder', [MinutesFileController::class, 'reorder'])->name('files.reorder');
    Route::delete('/{folder}/files/bulk-delete', [MinutesFileController::class, 'bulkDestroy'])->name('files.bulk-destroy');
    Route::post('/{folder}/files/{file}/verify', [MinutesFileController::class, 'verify'])->name('files.verify');
    Route::get('/{folder}/files/{file}', [MinutesFileController::class, 'show'])->name('files.show');
    Route::delete('/{folder}/files/{file}', [MinutesFileController::class, 'destroy'])->name('files.destroy');
});
