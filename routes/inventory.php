<?php
use App\Http\Controllers\Admin\InventoryController;
use Illuminate\Support\Facades\Route;
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function(){
 Route::get('/inventory',[InventoryController::class,'index'])->name('inventory.index');
 Route::get('/inventory/create',[InventoryController::class,'create'])->name('inventory.create');
 Route::post('/inventory',[InventoryController::class,'store'])->name('inventory.store');
 Route::get('/inventory/{inventory}',[InventoryController::class,'show'])->name('inventory.show');
 Route::get('/inventory/{inventory}/edit',[InventoryController::class,'edit'])->name('inventory.edit');
 Route::put('/inventory/{inventory}',[InventoryController::class,'update'])->name('inventory.update');
 Route::post('/inventory/{inventory}/receive',[InventoryController::class,'receive'])->name('inventory.receive');
 Route::post('/inventory/{inventory}/issue',[InventoryController::class,'issue'])->name('inventory.issue');
 Route::post('/inventory/{inventory}/adjust',[InventoryController::class,'adjust'])->name('inventory.adjust');
 Route::post('/inventory/{inventory}/reserve',[InventoryController::class,'reserve'])->name('inventory.reserve');
 Route::post('/inventory/{inventory}/release-reservation',[InventoryController::class,'releaseReservation'])->name('inventory.releaseReservation');
 Route::delete('/inventory/{inventory}',[InventoryController::class,'destroy'])->name('inventory.destroy');
});
