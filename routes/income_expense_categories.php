<?php

use App\Http\Controllers\Admin\IncomeExpenseCategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/income-expenses/categories')
    ->name('admin.income-expenses.categories.')
    ->middleware('admin')
    ->group(function () {
        Route::get('/', [IncomeExpenseCategoryController::class, 'index'])->name('index');

        Route::get('/income/create', [IncomeExpenseCategoryController::class, 'createIncome'])->name('income.create');
        Route::post('/income', [IncomeExpenseCategoryController::class, 'storeIncome'])->name('income.store');
        Route::get('/income/{category}/edit', [IncomeExpenseCategoryController::class, 'editIncome'])->name('income.edit');
        Route::put('/income/{category}', [IncomeExpenseCategoryController::class, 'updateIncome'])->name('income.update');
        Route::patch('/income/{category}/toggle', [IncomeExpenseCategoryController::class, 'toggleIncome'])->name('income.toggle');

        Route::get('/expense/create', [IncomeExpenseCategoryController::class, 'createExpense'])->name('expense.create');
        Route::post('/expense', [IncomeExpenseCategoryController::class, 'storeExpense'])->name('expense.store');
        Route::get('/expense/{category}/edit', [IncomeExpenseCategoryController::class, 'editExpense'])->name('expense.edit');
        Route::put('/expense/{category}', [IncomeExpenseCategoryController::class, 'updateExpense'])->name('expense.update');
        Route::patch('/expense/{category}/toggle', [IncomeExpenseCategoryController::class, 'toggleExpense'])->name('expense.toggle');
    });
