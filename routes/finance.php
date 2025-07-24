<?php
// routes/finance.php - เวอร์ชันที่แก้ไขแล้ว
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\InvestmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('finance')->name('finance.')->group(function () {
    
    // Main Dashboard
    Route::get('/', [FinanceController::class, 'dashboard'])->name('dashboard');
    
    // Accounts Management
    Route::resource('accounts', AccountController::class);
    
    // Assets Management
    Route::resource('assets', AssetController::class);
    
    // Transactions
    Route::get('/transactions', [FinanceController::class, 'transactions'])->name('transactions');
    Route::resource('transactions', TransactionController::class)->except(['index']);
    
    // Transfers
    Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    
    // Investment Management
    Route::prefix('investments')->name('investments.')->group(function () {
        Route::get('/', [InvestmentController::class, 'index'])->name('index');
        Route::get('/buy', [InvestmentController::class, 'buy'])->name('buy');
        Route::post('/buy', [InvestmentController::class, 'storeBuy'])->name('store-buy');
        Route::get('/sell', [InvestmentController::class, 'sell'])->name('sell');
        Route::post('/sell', [InvestmentController::class, 'storeSell'])->name('store-sell');
        Route::get('/dividend', [InvestmentController::class, 'dividend'])->name('dividend');
        Route::post('/dividend', [InvestmentController::class, 'storeDividend'])->name('store-dividend');
    });
    
    // Budgets (เดิม)
    Route::get('/budgets', [FinanceController::class, 'budgets'])->name('budgets');
    
    // Goals (เดิม)
    Route::get('/goals', [FinanceController::class, 'goals'])->name('goals');
    
    // Reports
    Route::get('/reports', [FinanceController::class, 'reports'])->name('reports');
    Route::get('/net-worth', [FinanceController::class, 'netWorth'])->name('net-worth');
    
    // API Routes for AJAX
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/accounts/{account}/balance', [AccountController::class, 'getBalance']);
        Route::post('/accounts/{account}/update-balance', [AccountController::class, 'updateBalance']);
        Route::get('/assets/{asset}/current-price', [AssetController::class, 'getCurrentPrice']);
        Route::post('/assets/{asset}/update-value', [AssetController::class, 'updateValue']);
    });
});