<?php

use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;

use App\Http\Controllers\Admin\ProduLeadControllerctController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\Customer\DashboardController as CustomerController;
Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::middleware('guest')->group(function () {

            Route::get(
                '/login',
                [AdminLoginController::class, 'create']
            )->name('login');

            Route::post(
                '/login',
                [AdminLoginController::class, 'store']
            )->name('login.store');

        });


        Route::middleware('admin')->group(function () {

            Route::get(
                '/dashboard',
                [DashboardController::class, 'index']
            )->name('dashboard');

            Route::post(
                '/logout',
                [AdminLoginController::class, 'destroy']
            )->name('logout');

               
            Route::get(
                '/products',
                [ProductController::class, 'index']
            )->name('products.index');

        });

        Route::get(
        '/leads',
        [LeadController::class, 'index']
    )->name('leads.index');

    Route::get(
        '/leads/create',
        [LeadController::class, 'create']
    )->name('leads.create');

    Route::post(
        '/leads',
        [LeadController::class, 'store']
    )->name('leads.store');

    Route::get(
        '/leads/{lead}',
        [LeadController::class, 'show']
    )->name('leads.show');

    Route::get(
        '/leads/{lead}/edit',
        [LeadController::class, 'edit']
    )->name('leads.edit');

    Route::put(
        '/leads/{lead}',
        [LeadController::class, 'update']
    )->name('leads.update');

    Route::delete(
        '/leads/{lead}',
        [LeadController::class, 'destroy']
    )->name('leads.destroy');


    Route::post(
        '/leads/{lead}/activity',
        [LeadController::class, 'activity']
    )->name('leads.activity');


    Route::post(
        '/leads/{lead}/status',
        [LeadController::class, 'status']
    )->name('leads.status');


    Route::post(
        '/leads/{lead}/convert',
        [LeadController::class, 'convert']
    )->name('leads.convert');


    });

  


    /*
        |--------------------------------------------------------------------------
        | Website Route
        |--------------------------------------------------------------------------
        */

        Route::get('/', [WebsiteController::class,'index']);
        Route::get('/customer-dashboard', [CustomerController::class,'index'])->name('customer.main');

        

// require __DIR__ . '/auth.php';
