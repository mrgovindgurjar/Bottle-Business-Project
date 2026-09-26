<?php

use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\CustomerController;

use App\Http\Controllers\Admin\ProductController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\Customer\DashboardController as CustomerPortalController;
 use App\Http\Controllers\Admin\ProductPriceController;
use App\Http\Controllers\Admin\PricingController;

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

            Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
            Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
            Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
            Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
            Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

            Route::post('/customers/{customer}/addresses', [CustomerController::class, 'storeAddress'])->name('customers.addresses.store');
            Route::put('/customers/{customer}/addresses/{address}', [CustomerController::class, 'updateAddress'])->name('customers.addresses.update');
            Route::delete('/customers/{customer}/addresses/{address}', [CustomerController::class, 'destroyAddress'])->name('customers.addresses.destroy');
            Route::post('/customers/{customer}/addresses/{address}/default', [CustomerController::class, 'setDefaultAddress'])->name('customers.addresses.default');

             Route::get('/products', [ProductController::class, 'index'])->name('products.index');
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

            Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
            Route::post('/products/{product}/prices', [ProductPriceController::class, 'store'])->name('products.prices.store');
            Route::put('/product-prices/{productPrice}', [ProductPriceController::class, 'update'])->name('products.prices.update');
            Route::delete('/product-prices/{productPrice}', [ProductPriceController::class, 'destroy'])->name('products.prices.destroy');


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
        Route::middleware(['auth', 'customer'])->group(function () {
            Route::get('/customer-dashboard', [CustomerPortalController::class,'index'])->name('customer.main');
            Route::put('/customer-dashboard/profile', [CustomerPortalController::class,'updateProfile'])->name('customer.profile.update');
        });

        Route::get('/dashboard', function () {
            return auth()->user()->hasRole('customer')
                ? redirect()->route('customer.main')
                : redirect()->route('admin.dashboard');
        })->middleware('auth')->name('dashboard');

        

require __DIR__ . '/auth.php';

 require __DIR__.'/design-studio.php';

 require __DIR__.'/quotation.php';

 require __DIR__ . '/orders.php';

require __DIR__ . '/production.php';

require __DIR__ . '/batches.php';

require __DIR__ . '/inventory.php';
require __DIR__ . '/procurement.php';
require __DIR__ . '/deliveries.php';
require __DIR__ . '/payments.php';
require __DIR__ . '/invoices.php';
require __DIR__ . '/income_expenses.php';
require __DIR__ . '/income_expense_categories.php';
require __DIR__ . '/staff_attendance.php';
require __DIR__ . '/issues.php';
require __DIR__ . '/reports.php';




 
require __DIR__ . '/security.php';
