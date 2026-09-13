<?php

use App\Http\Controllers\Admin\DeliveryController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/deliveries', [DeliveryController::class,'index'])->name('deliveries.index');
    Route::get('/deliveries/create', [DeliveryController::class,'create'])->name('deliveries.create');
    Route::post('/deliveries', [DeliveryController::class,'store'])->name('deliveries.store');
    Route::get('/deliveries/{delivery}', [DeliveryController::class,'show'])->name('deliveries.show');
    Route::get('/deliveries/{delivery}/edit', [DeliveryController::class,'edit'])->name('deliveries.edit');
    Route::put('/deliveries/{delivery}', [DeliveryController::class,'update'])->name('deliveries.update');
    Route::delete('/deliveries/{delivery}', [DeliveryController::class,'destroy'])->name('deliveries.destroy');
    Route::post('/deliveries/{delivery}/dispatch', [DeliveryController::class,'dispatch'])->name('deliveries.dispatch');
    Route::post('/deliveries/{delivery}/status', [DeliveryController::class,'status'])->name('deliveries.status');
    Route::post('/deliveries/{delivery}/cancel', [DeliveryController::class,'cancel'])->name('deliveries.cancel');
    Route::get('/deliveries/{delivery}/print', [DeliveryController::class,'print'])->name('deliveries.print');
    Route::get('/delivery-data/orders/{order}', [DeliveryController::class,'orderData'])->name('deliveries.order-data');
});
