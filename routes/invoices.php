<?php

use App\Http\Controllers\Admin\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin')->prefix('admin/invoices')->name('admin.invoices.')->group(function () {
    Route::get('/', [InvoiceController::class,'index'])->name('index');
    Route::get('/create', [InvoiceController::class,'create'])->name('create');
    Route::post('/', [InvoiceController::class,'store'])->name('store');
    Route::get('/{invoice}', [InvoiceController::class,'show'])->name('show');
    Route::get('/{invoice}/edit', [InvoiceController::class,'edit'])->name('edit');
    Route::put('/{invoice}', [InvoiceController::class,'update'])->name('update');
    Route::post('/{invoice}/issue', [InvoiceController::class,'issue'])->name('issue');
    Route::post('/{invoice}/send', [InvoiceController::class,'send'])->name('send');
    Route::post('/{invoice}/cancel', [InvoiceController::class,'cancel'])->name('cancel');
    Route::post('/{invoice}/duplicate', [InvoiceController::class,'duplicate'])->name('duplicate');
    Route::delete('/{invoice}', [InvoiceController::class,'destroy'])->name('destroy');
    Route::get('/{invoice}/print', [InvoiceController::class,'print'])->name('print');
    Route::get('/{invoice}/pdf', [InvoiceController::class,'pdf'])->name('pdf');
});

Route::middleware('admin')->prefix('admin/orders')->name('admin.orders.')->group(function () {
    Route::get('/{order}/invoice', [InvoiceController::class,'createFromOrder'])->name('invoice');
});
