<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\IssueController;

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function(){
    Route::get('issues', [IssueController::class,'index'])->name('issues.index');
    Route::get('issues/create', [IssueController::class,'create'])->name('issues.create');
    Route::post('issues', [IssueController::class,'store'])->name('issues.store');

    // Static/category routes must come before /issues/{issue}.
    Route::get('issues/categories/manage', [IssueController::class,'categories'])->name('issues.categories');
    Route::post('issues/categories', [IssueController::class,'categoryStore'])->name('issues.categories.store');
    Route::put('issues/categories/{category}', [IssueController::class,'categoryUpdate'])->name('issues.categories.update');
    Route::post('issues/categories/{category}/toggle', [IssueController::class,'categoryToggle'])->name('issues.categories.toggle');

    Route::get('issues/{issue}', [IssueController::class,'show'])->name('issues.show');
    Route::get('issues/{issue}/edit', [IssueController::class,'edit'])->name('issues.edit');
    Route::put('issues/{issue}', [IssueController::class,'update'])->name('issues.update');
    Route::delete('issues/{issue}', [IssueController::class,'destroy'])->name('issues.destroy');
    Route::post('issues/{issue}/status', [IssueController::class,'status'])->name('issues.status');
    Route::post('issues/{issue}/assign', [IssueController::class,'assign'])->name('issues.assign');
    Route::post('issues/{issue}/comments', [IssueController::class,'comment'])->name('issues.comments.store');
});
