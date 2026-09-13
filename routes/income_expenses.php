<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\IncomeExpenseController;

Route::prefix('admin/income-expenses')->name('admin.income-expenses.')->middleware('admin')->group(function(){
    Route::get('/',[IncomeExpenseController::class,'index'])->name('index');
    Route::get('/income/create',[IncomeExpenseController::class,'createIncome'])->name('create-income');
    Route::post('/income',[IncomeExpenseController::class,'storeIncome'])->name('store-income');
    Route::get('/income/{income}',[IncomeExpenseController::class,'showIncome'])->name('show-income');
    Route::get('/income/{income}/edit',[IncomeExpenseController::class,'editIncome'])->name('edit-income');
    Route::put('/income/{income}',[IncomeExpenseController::class,'updateIncome'])->name('update-income');
    Route::post('/income/{income}/cancel',[IncomeExpenseController::class,'cancelIncome'])->name('cancel-income');
    Route::get('/expense/create',[IncomeExpenseController::class,'createExpense'])->name('create-expense');
    Route::post('/expense',[IncomeExpenseController::class,'storeExpense'])->name('store-expense');
    Route::get('/expense/{expense}',[IncomeExpenseController::class,'showExpense'])->name('show-expense');
    Route::get('/expense/{expense}/edit',[IncomeExpenseController::class,'editExpense'])->name('edit-expense');
    Route::put('/expense/{expense}',[IncomeExpenseController::class,'updateExpense'])->name('update-expense');
    Route::post('/expense/{expense}/cancel',[IncomeExpenseController::class,'cancelExpense'])->name('cancel-expense');
});
