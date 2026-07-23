<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoticeFolderController;
use App\Http\Controllers\NoticeFileController;

Route::middleware(['auth'])->prefix('notice')->name('notice.')->group(function () {
    Route::get('/', [NoticeFolderController::class, 'index'])->name('index');
    Route::get('/create', [NoticeFolderController::class, 'create'])->name('create');
    Route::post('/', [NoticeFolderController::class, 'store'])->name('store');
    Route::get('/{folder}', [NoticeFolderController::class, 'show'])->name('show');
    Route::get('/{folder}/edit', [NoticeFolderController::class, 'edit'])->name('edit');
    Route::put('/{folder}', [NoticeFolderController::class, 'update'])->name('update');
    Route::delete('/{folder}', [NoticeFolderController::class, 'destroy'])->name('destroy');

    Route::post('/{folder}/files', [NoticeFileController::class, 'store'])->name('files.store');
    Route::post('/{folder}/files/reorder', [NoticeFileController::class, 'reorder'])->name('files.reorder');
    Route::delete('/{folder}/files/bulk-delete', [NoticeFileController::class, 'bulkDestroy'])->name('files.bulk-destroy');
    Route::post('/{folder}/files/{file}/verify', [NoticeFileController::class, 'verify'])->name('files.verify');
    Route::get('/{folder}/files/{file}', [NoticeFileController::class, 'show'])->name('files.show');
    Route::delete('/{folder}/files/{file}', [NoticeFileController::class, 'destroy'])->name('files.destroy');
});
