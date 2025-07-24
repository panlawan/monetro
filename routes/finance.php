<?php
// routes/finance.php (สร้างไฟล์ใหม่)

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FinancialGoalController;
use Illuminate\Support\Facades\Route;

// Finance Routes - Protected by auth middleware
Route::middleware(['auth', 'verified'])->prefix('finance')->name('finance.')->group(function () {
    
    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::post('/transactions/import', [TransactionController::class, 'bulkImport'])->name('transactions.import');
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/defaults', [CategoryController::class, 'createDefaultCategories'])->name('categories.defaults');
    
    // Budgets
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
    
    // Goals
    Route::get('/goals', [FinancialGoalController::class, 'index'])->name('goals');
    Route::post('/goals', [FinancialGoalController::class, 'store'])->name('goals.store');
    Route::put('/goals/{goal}', [FinancialGoalController::class, 'update'])->name('goals.update');
    Route::delete('/goals/{goal}', [FinancialGoalController::class, 'destroy'])->name('goals.destroy');
});