<?php
// app/Http/Controllers/Api/FinanceApiController.php - API Controller

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budget;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinanceApiController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $user = auth()->user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Monthly Summary
        $monthlyIncome = Transaction::forUser($user->id)
            ->income()
            ->thisMonth()
            ->sum('amount');

        $monthlyExpense = Transaction::forUser($user->id)
            ->expense()
            ->thisMonth()
            ->sum('amount');

        $monthlyBalance = $monthlyIncome - $monthlyExpense;
        $savingsRate = $monthlyIncome > 0 ? ($monthlyBalance / $monthlyIncome) * 100 : 0;

        // Recent Transactions
        $recentTransactions = Transaction::forUser($user->id)
            ->with('category')
            ->latest('transaction_date')
            ->take(5)
            ->get();

        return response()->json([
            'summary' => [
                'monthly_income' => $monthlyIncome,
                'monthly_expense' => $monthlyExpense,
                'monthly_balance' => $monthlyBalance,
                'savings_rate' => round($savingsRate, 2),
            ],
            'recent_transactions' => $recentTransactions,
            'currency' => 'THB'
        ]);
    }

    public function categories(Request $request): JsonResponse
    {
        $query = Category::forUser(auth()->id())->active();
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        $categories = $query->orderBy('sort_order')->get();
        
        return response()->json($categories);
    }

    public function budgets(): JsonResponse
    {
        $user = auth()->user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->with('category')
            ->get()
            ->map(function ($budget) {
                return [
                    'id' => $budget->id,
                    'category' => $budget->category->name,
                    'budgeted' => $budget->amount,
                    'spent' => $budget->getSpentAmount(),
                    'remaining' => $budget->getRemainingAmount(),
                    'percentage' => $budget->getUsagePercentage(),
                    'over_budget' => $budget->isOverBudget(),
                    'color' => $budget->category->color
                ];
            });

        return response()->json($budgets);
    }

    public function goals(): JsonResponse
    {
        $goals = Goal::where('user_id', auth()->id())
            ->active()
            ->orderBy('target_date')
            ->get()
            ->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'progress_percentage' => $goal->progress_percentage,
                    'remaining_amount' => $goal->remaining_amount,
                    'days_remaining' => $goal->days_remaining,
                    'target_date' => $goal->target_date->format('Y-m-d'),
                    'color' => $goal->color,
                    'icon' => $goal->icon
                ];
            });

        return response()->json($goals);
    }
}