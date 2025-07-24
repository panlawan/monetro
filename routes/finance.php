<?php
// routes/finance.php

use App\Http\Controllers\FinanceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\GoalController;
use Illuminate\Support\Facades\Route;

// Finance Dashboard Routes - ใส่ middleware ที่ route แทน
Route::middleware(['auth', 'verified'])->prefix('finance')->name('finance.')->group(function () {
    
    // Main Finance Dashboard
    Route::get('/', [FinanceController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');
    
    // Transactions
    Route::get('/transactions', [FinanceController::class, 'transactions'])->name('transactions');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    
    // Goals
    Route::get('/goals', [FinanceController::class, 'goals'])->name('goals');
    Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
    Route::get('/goals/{goal}', [GoalController::class, 'show'])->name('goals.show');
    Route::put('/goals/{goal}', [GoalController::class, 'update'])->name('goals.update');
    Route::delete('/goals/{goal}', [GoalController::class, 'destroy'])->name('goals.destroy');
    Route::post('/goals/{goal}/progress', [GoalController::class, 'addProgress'])->name('goals.progress');
    Route::post('/goals/{goal}/complete', [GoalController::class, 'markCompleted'])->name('goals.complete');
    
    // Budgets
    Route::get('/budgets', [FinanceController::class, 'budgets'])->name('budgets');
    
    // Reports
    Route::get('/reports', [FinanceController::class, 'reports'])->name('reports');
});