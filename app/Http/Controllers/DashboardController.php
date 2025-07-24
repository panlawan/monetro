<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budget;
use App\Models\FinancialGoal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $currentMonth = Carbon::now();
        
        // Monthly Summary
        $monthlyData = $this->getMonthlyData($userId, $currentMonth);
        
        // Recent Transactions (last 10)
        $recentTransactions = Transaction::forUser($userId)
            ->with('category')
            ->latest('transaction_date')
            ->take(10)
            ->get();
        
        // Monthly Trend (last 6 months)
        $monthlyTrend = Transaction::getMonthlyTrend($userId, 6);
        
        // Expense by Category (current month)
        $expenseByCategory = Transaction::getExpenseByCategory(
            $userId, 
            $currentMonth->startOfMonth()->toDateString(),
            $currentMonth->endOfMonth()->toDateString()
        );
        
        // Active Goals
        $goals = FinancialGoal::where('user_id', $userId)
            ->where('status', 'active')
            ->take(5)
            ->get()
            ->map(function($goal) {
                $goal->updateProgress();
                return $goal;
            });
        
        // Active Budgets
        $budgets = Budget::where('user_id', $userId)
            ->where('is_active', true)
            ->whereBetween('start_date', [
                $currentMonth->startOfMonth()->toDateString(),
                $currentMonth->endOfMonth()->toDateString()
            ])
            ->with('category')
            ->get()
            ->map(function($budget) {
                $budget->updateSpent();
                return $budget;
            });
        
        return view('dashboard', array_merge($monthlyData, [
            'recentTransactions' => $recentTransactions,
            'monthlyTrend' => $monthlyTrend,
            'expenseByCategory' => $expenseByCategory,
            'goals' => $goals,
            'budgets' => $budgets,
        ]));
    }
    
    private function getMonthlyData($userId, $month)
    {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        
        // Income & Expense for current month
        $monthlyIncome = Transaction::forUser($userId)
            ->income()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
            
        $monthlyExpense = Transaction::forUser($userId)
            ->expense()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
            
        $monthlyBalance = $monthlyIncome + $monthlyExpense; // expense is negative
        
        // Savings Rate
        $savingsRate = $monthlyIncome > 0 
            ? (($monthlyIncome + $monthlyExpense) / $monthlyIncome) * 100 
            : 0;
        
        return [
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'monthlyBalance' => $monthlyBalance,
            'savingsRate' => $savingsRate,
        ];
    }
}