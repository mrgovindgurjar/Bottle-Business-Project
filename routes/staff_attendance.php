<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AttendanceController;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('staff', StaffController::class)->except(['show','destroy'])->names('staff');
    Route::get('staff/{staff}', [StaffController::class,'show'])->name('staff.show');
    Route::delete('staff/{staff}', [StaffController::class,'destroy'])->name('staff.destroy');

    Route::get('attendance', [AttendanceController::class,'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class,'store'])->name('attendance.store');
    Route::post('attendance/bulk', [AttendanceController::class,'bulkMark'])->name('attendance.bulk');
    Route::get('attendance/{attendance}/edit', [AttendanceController::class,'edit'])->name('attendance.edit');
    Route::put('attendance/{attendance}', [AttendanceController::class,'update'])->name('attendance.update');
    Route::get('staff/{staff}/attendance', [AttendanceController::class,'history'])->name('staff.attendance.history');
});
