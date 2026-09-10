<?php

use App\Http\Controllers\Admin\DesignStudioController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/design-studio', [DesignStudioController::class, 'index'])->name('designs.index');
    Route::get('/design-studio/create', [DesignStudioController::class, 'create'])->name('designs.create');
    Route::post('/design-studio', [DesignStudioController::class, 'store'])->name('designs.store');
    Route::get('/design-studio/{design}', [DesignStudioController::class, 'show'])->name('designs.show');
    Route::get('/design-studio/{design}/edit', [DesignStudioController::class, 'edit'])->name('designs.edit');
    Route::put('/design-studio/{design}/versions/{version}', [DesignStudioController::class, 'save'])->name('designs.save');
    Route::post('/design-studio/{design}/versions/{version}/new', [DesignStudioController::class, 'newVersion'])->name('designs.new-version');
    Route::post('/design-studio/{design}/versions/{version}/logo', [DesignStudioController::class, 'logo'])->name('designs.logo');
    Route::post('/design-studio/{design}/versions/{version}/submit', [DesignStudioController::class, 'submit'])->name('designs.submit');
    Route::post('/design-studio/{design}/versions/{version}/approve', [DesignStudioController::class, 'approve'])->name('designs.approve');
    Route::post('/design-studio/{design}/versions/{version}/changes', [DesignStudioController::class, 'changes'])->name('designs.changes');
    Route::post('/design-studio/{design}/versions/{version}/comment', [DesignStudioController::class, 'comment'])->name('designs.comment');
});
