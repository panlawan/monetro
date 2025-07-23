<?php
// routes/finance.php (สร้างไฟล์ใหม่)

use App\Http\Controllers\FinanceController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Finance Dashboard Routes
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
    
    // Budgets
    Route::get('/budgets', [FinanceController::class, 'budgets'])->name('budgets');
    
    // Goals
    Route::get('/goals', [FinanceController::class, 'goals'])->name('goals');
    
    // Reports
    Route::get('/reports', [FinanceController::class, 'reports'])->name('reports');
});

---

// เพิ่มในไฟล์ routes/web.php หลังจาก require __DIR__.'/admin.php';

// Include finance routes
require __DIR__.'/finance.php';