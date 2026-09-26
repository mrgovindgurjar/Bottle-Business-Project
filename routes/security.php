<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SecurityRoleController;
use App\Http\Controllers\Admin\SecurityUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [SecurityUserController::class, 'index'])->name('security.users.index');
    Route::get('/users/create', [SecurityUserController::class, 'create'])->name('security.users.create');
    Route::post('/users', [SecurityUserController::class, 'store'])->name('security.users.store');
    Route::get('/users/{user}/edit', [SecurityUserController::class, 'edit'])->name('security.users.edit');
    Route::put('/users/{user}', [SecurityUserController::class, 'update'])->name('security.users.update');
    Route::post('/users/{user}/reset-password', [SecurityUserController::class, 'resetPassword'])->name('security.users.reset-password');
    Route::delete('/users/{user}', [SecurityUserController::class, 'destroy'])->name('security.users.destroy');

    Route::get('/roles', [SecurityRoleController::class, 'index'])->name('security.roles.index');
    Route::get('/roles/create', [SecurityRoleController::class, 'create'])->name('security.roles.create');
    Route::post('/roles', [SecurityRoleController::class, 'store'])->name('security.roles.store');
    Route::get('/roles/{role}/edit', [SecurityRoleController::class, 'edit'])->name('security.roles.edit');
    Route::put('/roles/{role}', [SecurityRoleController::class, 'update'])->name('security.roles.update');
    Route::delete('/roles/{role}', [SecurityRoleController::class, 'destroy'])->name('security.roles.destroy');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('security.audit.index');

    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
});
