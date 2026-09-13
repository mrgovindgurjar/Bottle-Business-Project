<?php

use App\Http\Controllers\Admin\BatchController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::get('/batches/create', [BatchController::class, 'create'])->name('batches.create');
    Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
    Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
    Route::get('/batches/{batch}/edit', [BatchController::class, 'edit'])->name('batches.edit');
    Route::put('/batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
    Route::delete('/batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');
    Route::post('/batches/{batch}/release', [BatchController::class, 'release'])->name('batches.release');
    Route::post('/batches/{batch}/block', [BatchController::class, 'block'])->name('batches.block');
    Route::post('/batches/{batch}/allocate', [BatchController::class, 'allocate'])->name('batches.allocate');
});
