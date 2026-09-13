<?php
use Illuminate\Support\Facades\Route; use App\Http\Controllers\Admin\PaymentController;
Route::middleware('admin')->prefix('admin/payments')->name('admin.payments.')->group(function(){
 Route::get('/',[PaymentController::class,'index'])->name('index'); Route::get('/create',[PaymentController::class,'create'])->name('create'); Route::post('/',[PaymentController::class,'store'])->name('store');
 Route::get('/customer-ledger',[PaymentController::class,'customerLedger'])->name('customer-ledger'); Route::get('/supplier-ledger',[PaymentController::class,'supplierLedger'])->name('supplier-ledger');
 Route::get('/{payment}',[PaymentController::class,'show'])->name('show'); Route::get('/{payment}/edit',[PaymentController::class,'edit'])->name('edit'); Route::put('/{payment}',[PaymentController::class,'update'])->name('update');
 Route::post('/{payment}/cancel',[PaymentController::class,'cancel'])->name('cancel'); Route::post('/{payment}/allocate',[PaymentController::class,'allocate'])->name('allocate'); Route::post('/{payment}/refund',[PaymentController::class,'refund'])->name('refund'); Route::post('/{payment}/approve',[PaymentController::class,'approve'])->name('approve');
 Route::get('/{payment}/receipt',[PaymentController::class,'receipt'])->name('receipt'); Route::get('/{payment}/pdf',[PaymentController::class,'pdf'])->name('pdf');
});
Route::middleware('admin')->post('admin/payment-allocations/{allocation}/deallocate',[PaymentController::class,'deallocate'])->name('admin.payments.deallocate');
