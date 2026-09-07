<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LeadActivityController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductPriceController;



Route::get('/', [WebsiteController::class,'index'])->name('main.page');

Route::get('/customer-panel',[DashboardController::class,'index'])->name('customer.main');

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('leads', LeadController::class);

        Route::post(
            'leads/{lead}/activities',
            [LeadActivityController::class, 'store']
        )->name('leads.activities.store');

        Route::resource(
            'products',
            ProductController::class
        );

        Route::post(
            'products/{product}/prices',
            [ProductPriceController::class, 'store']
        )->name('products.prices.store');

        Route::delete(
            'product-prices/{productPrice}',
            [ProductPriceController::class, 'destroy']
        )->name('product-prices.destroy');

    });
    

    
 