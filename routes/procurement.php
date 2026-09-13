<?php
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SupplierController;
use Illuminate\Support\Facades\Route;
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/suppliers',[SupplierController::class,'index'])->name('suppliers.index');
    Route::get('/suppliers/create',[SupplierController::class,'create'])->name('suppliers.create');
    Route::post('/suppliers',[SupplierController::class,'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}',[SupplierController::class,'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit',[SupplierController::class,'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}',[SupplierController::class,'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}',[SupplierController::class,'destroy'])->name('suppliers.destroy');
    Route::get('/purchases',[PurchaseController::class,'index'])->name('purchases.index');
    Route::get('/purchases/create',[PurchaseController::class,'create'])->name('purchases.create');
    Route::post('/purchases',[PurchaseController::class,'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}',[PurchaseController::class,'show'])->name('purchases.show');
    Route::get('/purchases/{purchase}/edit',[PurchaseController::class,'edit'])->name('purchases.edit');
    Route::put('/purchases/{purchase}',[PurchaseController::class,'update'])->name('purchases.update');
    Route::delete('/purchases/{purchase}',[PurchaseController::class,'destroy'])->name('purchases.destroy');
    Route::post('/purchases/{purchase}/duplicate',[PurchaseController::class,'duplicate'])->name('purchases.duplicate');
    Route::post('/purchases/{purchase}/receive',[PurchaseController::class,'receive'])->name('purchases.receive');
    Route::post('/purchases/{purchase}/pay',[PurchaseController::class,'pay'])->name('purchases.pay');
    Route::post('/purchases/{purchase}/cancel',[PurchaseController::class,'cancel'])->name('purchases.cancel');
    Route::get('/purchases/{purchase}/pdf',[PurchaseController::class,'pdf'])->name('purchases.pdf');
    Route::get('/purchase-data/price',[PurchaseController::class,'price'])->name('purchases.price');
    Route::get('/purchase-data/inventory-items',[PurchaseController::class,'inventoryItems'])->name('purchases.inventory-items');
});
