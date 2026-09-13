<?php

use App\Http\Controllers\Admin\QuotationController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/quotations', [QuotationController::class,'index'])->name('quotations.index');
    Route::get('/quotations/create', [QuotationController::class,'create'])->name('quotations.create');
    Route::post('/quotations', [QuotationController::class,'store'])->name('quotations.store');
    Route::get('/quotations/{quotation}', [QuotationController::class,'show'])->name('quotations.show');
    Route::get('/quotations/{quotation}/edit', [QuotationController::class,'edit'])->name('quotations.edit');
    Route::put('/quotations/{quotation}', [QuotationController::class,'update'])->name('quotations.update');
    Route::delete('/quotations/{quotation}', [QuotationController::class,'destroy'])->name('quotations.destroy');
    Route::post('/quotations/{quotation}/send', [QuotationController::class,'send'])->name('quotations.send');
    Route::post('/quotations/{quotation}/approve', [QuotationController::class,'approve'])->name('quotations.approve');
    Route::post('/quotations/{quotation}/reject', [QuotationController::class,'reject'])->name('quotations.reject');
    Route::post('/quotations/{quotation}/duplicate', [QuotationController::class,'duplicate'])->name('quotations.duplicate');
    Route::get('/quotations/{quotation}/preview', [QuotationController::class,'preview'])->name('quotations.preview');
    Route::get('/quotations/{quotation}/pdf', [QuotationController::class,'pdf'])->name('quotations.pdf');
    Route::post('/quotations/{quotation}/convert', [QuotationController::class,'convert'])->name('quotations.convert');
    Route::get('/quotation-data/price', [QuotationController::class,'price'])->name('quotations.price');
    Route::get('/quotation-data/designs', [QuotationController::class,'designs'])->name('quotations.designs');
});
