<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\SalaryPaymentController;

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

    Route::get('salary', [SalaryPaymentController::class,'index'])->name('salary.index');
    Route::get('salary/create', [SalaryPaymentController::class,'create'])->name('salary.create');
    Route::post('salary', [SalaryPaymentController::class,'store'])->name('salary.store');
    Route::get('salary/{salary}', [SalaryPaymentController::class,'show'])->name('salary.show');
    Route::get('salary/{salary}/edit', [SalaryPaymentController::class,'edit'])->name('salary.edit');
    Route::put('salary/{salary}', [SalaryPaymentController::class,'update'])->name('salary.update');
    Route::post('salary/{salary}/pay', [SalaryPaymentController::class,'pay'])->name('salary.pay');
    Route::post('salary/{salary}/cancel', [SalaryPaymentController::class,'cancel'])->name('salary.cancel');
});
