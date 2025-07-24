<?php
// app/Http/Controllers/FinanceController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Show the finance dashboard
     */
    public function dashboard(): View
    {
        $user = auth()->user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Monthly Summary
        $monthlyIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount') ?? 0;

        $monthlyExpense = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount') ?? 0;

        $monthlyBalance = $monthlyIncome - $monthlyExpense;
        $savingsRate = $monthlyIncome > 0 ? ($monthlyBalance / $monthlyIncome) * 100 : 0;

        // Recent Transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('category')
            ->latest('transaction_date')
            ->take(10)
            ->get();

        // Monthly Trend (Last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('M Y');
            
            $income = Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount') ?? 0;
                
            $expense = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount') ?? 0;
                
            $monthlyTrend[] = [
                'month' => $month,
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense
            ];
        }

        // Category Breakdown (This Month)
        $expenseByCategory = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name ?? 'Unknown',
                    'amount' => $item->total,
                    'color' => $item->category->color ?? '#1cc88a',
                    'icon' => $item->category->icon ?? 'fa-circle'
                ];
            });

        // Budget Status
        $budgets = collect();

        // Active Goals
        $activeGoals = Goal::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('target_date')
            ->take(5)
            ->get();

        // Categories for Quick Add
        $incomeCategories = Category::where('user_id', $user->id)
            ->where('type', 'income')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $expenseCategories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('finance.dashboard', compact(
            'monthlyIncome',
            'monthlyExpense', 
            'monthlyBalance',
            'savingsRate',
            'recentTransactions',
            'monthlyTrend',
            'expenseByCategory',
            'budgets',
            'activeGoals',
            'incomeCategories',
            'expenseCategories'
        ));
    }

    /**
     * Show transactions page
     */
    public function transactions(Request $request): View
    {
        $user = auth()->user();
        
        $query = Transaction::where('user_id', $user->id)->with('category');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->latest('transaction_date')->paginate(20);

        // Get categories for filter dropdown
        $categories = Category::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('finance.transactions', compact('transactions', 'categories'));
    }

    /**
     * Show goals page
     */
    public function goals(): View
    {
        $user = auth()->user();
        
        $activeGoals = Goal::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('target_date')
            ->get();

        $completedGoals = Goal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('finance.goals', compact('activeGoals', 'completedGoals'));
    }

    /**
     * Show budgets page
     */
    public function budgets(): View
    {
        $user = auth()->user();
        $budgets = collect();
        $categories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('finance.budgets', compact('budgets', 'categories'));
    }

    /**
     * Show reports page
     */
    public function reports(): View
    {
        $user = auth()->user();
        
        // Monthly Income vs Expense (Last 12 months)
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('M Y');
            
            $income = Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount') ?? 0;
                
            $expense = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount') ?? 0;
                
            $monthlyData[] = [
                'month' => $month,
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense
            ];
        }

        // Category Analysis (Last 3 months)
        $categoryData = Transaction::where('user_id', $user->id)
            ->where('transaction_date', '>=', now()->subMonths(3))
            ->select('category_id', 'type', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('category_id', 'type')
            ->get()
            ->groupBy('type');

        // Top Expense Categories
        $topExpenseCategories = $categoryData->get('expense', collect())
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // Income Sources
        $incomeSources = $categoryData->get('income', collect())
            ->sortByDesc('total')
            ->values();

        return view('finance.reports', compact(
            'monthlyData',
            'topExpenseCategories',
            'incomeSources'
        ));
    }
}