<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LeadActivityController;


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
    });
    