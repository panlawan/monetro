<?php
// routes/api.php - API Routes for Mobile/AJAX

use App\Http\Controllers\Api\FinanceApiController;
use App\Http\Controllers\Api\TransactionApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard API
    Route::get('/dashboard', [FinanceApiController::class, 'dashboard']);
    Route::get('/dashboard/summary', [FinanceApiController::class, 'summary']);
    
    // Transactions API
    Route::apiResource('transactions', TransactionApiController::class);
    Route::get('/transactions/export/csv', [TransactionApiController::class, 'exportCsv']);
    
    // Categories API
    Route::get('/categories', [FinanceApiController::class, 'categories']);
    Route::get('/categories/{type}', [FinanceApiController::class, 'categoriesByType']);
    
    // Reports API
    Route::get('/reports/monthly/{year}/{month}', [FinanceApiController::class, 'monthlyReport']);
    Route::get('/reports/yearly/{year}', [FinanceApiController::class, 'yearlyReport']);
    
    // Budget API
    Route::get('/budgets', [FinanceApiController::class, 'budgets']);
    Route::get('/budgets/{month}/{year}', [FinanceApiController::class, 'budgetsByPeriod']);
    
    // Goals API
    Route::get('/goals', [FinanceApiController::class, 'goals']);
    Route::post('/goals/{goal}/progress', [FinanceApiController::class, 'addGoalProgress']);
});