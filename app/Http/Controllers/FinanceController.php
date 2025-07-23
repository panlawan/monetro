<?php
// app/Http/Controllers/FinanceController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budget;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the finance dashboard
     */
    public function dashboard(): View
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
            ->take(10)
            ->get();

        // Monthly Trend (Last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('M Y');
            
            $income = Transaction::forUser($user->id)
                ->income()
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');
                
            $expense = Transaction::forUser($user->id)
                ->expense()
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');
                
            $monthlyTrend[] = [
                'month' => $month,
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense
            ];
        }

        // Category Breakdown (This Month)
        $expenseByCategory = Transaction::forUser($user->id)
            ->expense()
            ->thisMonth()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name,
                    'amount' => $item->total,
                    'color' => $item->category->color,
                    'icon' => $item->category->icon
                ];
            });

        // Budget Status
        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->with('category')
            ->get()
            ->map(function ($budget) {
                return [
                    'category' => $budget->category->name,
                    'budgeted' => $budget->amount,
                    'spent' => $budget->getSpentAmount(),
                    'remaining' => $budget->getRemainingAmount(),
                    'percentage' => $budget->getUsagePercentage(),
                    'over_budget' => $budget->isOverBudget(),
                    'color' => $budget->category->color
                ];
            });

        // Active Goals
        $activeGoals = Goal::where('user_id', $user->id)
            ->active()
            ->orderBy('target_date')
            ->take(5)
            ->get();

        // Categories for Quick Add
        $incomeCategories = Category::forUser($user->id)
            ->income()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $expenseCategories = Category::forUser($user->id)
            ->expense()
            ->active()
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
        
        $query = Transaction::forUser($user->id)->with('category');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->latest('transaction_date')->paginate(20);

        // Get categories for filter
        $categories = Category::forUser($user->id)
            ->active()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        // Summary for filtered results
        $totalIncome = Transaction::forUser($user->id)->income();
        $totalExpense = Transaction::forUser($user->id)->expense();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $totalIncome->inDateRange($request->start_date, $request->end_date);
            $totalExpense->inDateRange($request->start_date, $request->end_date);
        }

        $summary = [
            'income' => $totalIncome->sum('amount'),
            'expense' => $totalExpense->sum('amount'),
        ];
        $summary['balance'] = $summary['income'] - $summary['expense'];

        return view('finance.transactions', compact(
            'transactions',
            'categories',
            'summary'
        ));
    }

    /**
     * Show budgets page
     */
    public function budgets(): View
    {
        $user = auth()->user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->with('category')
            ->get();

        $expenseCategories = Category::forUser($user->id)
            ->expense()
            ->active()
            ->orderBy('name')
            ->get();

        return view('finance.budgets', compact('budgets', 'expenseCategories'));
    }

    /**
     * Show goals page
     */
    public function goals(): View
    {
        $user = auth()->user();
        
        $goals = Goal::where('user_id', $user->id)
            ->orderBy('status')
            ->orderBy('target_date')
            ->get();

        return view('finance.goals', compact('goals'));
    }

    /**
     * Show reports page
     */
    public function reports(Request $request): View
    {
        $user = auth()->user();
        
        // Default to current year if no year specified
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        // Monthly report if month specified
        if ($month) {
            $startDate = Carbon::create($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();
            $reportTitle = $startDate->format('F Y');
        } else {
            // Yearly report
            $startDate = Carbon::create($year, 1, 1);
            $endDate = $startDate->copy()->endOfYear();
            $reportTitle = "Year $year";
        }

        // Income and Expense by Category
        $incomeByCategory = Transaction::forUser($user->id)
            ->income()
            ->inDateRange($startDate, $endDate)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        $expenseByCategory = Transaction::forUser($user->id)
            ->expense()
            ->inDateRange($startDate, $endDate)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        // Monthly breakdown if yearly report
        $monthlyBreakdown = [];
        if (!$month) {
            for ($m = 1; $m <= 12; $m++) {
                $monthDate = Carbon::create($year, $m, 1);
                
                $income = Transaction::forUser($user->id)
                    ->income()
                    ->whereMonth('transaction_date', $m)
                    ->whereYear('transaction_date', $year)
                    ->sum('amount');
                    
                $expense = Transaction::forUser($user->id)
                    ->expense()
                    ->whereMonth('transaction_date', $m)
                    ->whereYear('transaction_date', $year)
                    ->sum('amount');
                    
                $monthlyBreakdown[] = [
                    'month' => $monthDate->format('M'),
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $income - $expense
                ];
            }
        }

        // Summary
        $totalIncome = $incomeByCategory->sum('total');
        $totalExpense = $expenseByCategory->sum('total');
        $netBalance = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? ($netBalance / $totalIncome) * 100 : 0;

        return view('finance.reports', compact(
            'incomeByCategory',
            'expenseByCategory',
            'monthlyBreakdown',
            'totalIncome',
            'totalExpense',
            'netBalance',
            'savingsRate',
            'reportTitle',
            'year',
            'month'
        ));
    }
}