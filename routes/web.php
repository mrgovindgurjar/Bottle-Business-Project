<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\Customer\DashboardController;

Route::get('/', [WebsiteController::class,'index'])->name('main.page');

Route::get('/customer-panel',[DashboardController::class,'index'])->name('customer.main');
