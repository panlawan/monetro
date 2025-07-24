<?php
// app/Http/Controllers/BudgetController.php
namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        $budgets = Budget::where('user_id', $userId)
            ->with('category')
            ->latest()
            ->get()
            ->map(function($budget) {
                $budget->updateSpent();
                return $budget;
            });
        
        $categories = Category::forUser($userId)
            ->byType('expense')
            ->active()
            ->get();
        
        return view('finance.budgets.index', compact('budgets', 'categories'));
    }
    
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'category_id' => 'required|exists:categories,id',
            'alert_percentage' => 'nullable|numeric|min:1|max:100',
        ]);
        
        // Calculate start and end dates based on period
        $dates = $this->calculateBudgetDates($request->period);
        
        $budget = Budget::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'period' => $request->period,
            'start_date' => $dates['start'],
            'end_date' => $dates['end'],
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
            'alert_percentage' => $request->alert_percentage ?? 80,
            'alert_enabled' => true,
            'is_active' => true,
        ]);
        
        $budget->updateSpent();
        
        return response()->json([
            'success' => true,
            'message' => 'Budget created successfully',
            'budget' => $budget->load('category')
        ]);
    }
    
    public function update(Request $request, Budget $budget): JsonResponse
    {
        if ($budget->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'alert_percentage' => 'nullable|numeric|min:1|max:100',
            'is_active' => 'boolean',
        ]);
        
        $budget->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'alert_percentage' => $request->alert_percentage ?? $budget->alert_percentage,
            'is_active' => $request->is_active ?? $budget->is_active,
        ]);
        
        $budget->updateSpent();
        
        return response()->json([
            'success' => true,
            'message' => 'Budget updated successfully',
            'budget' => $budget->load('category')
        ]);
    }
    
    public function destroy(Budget $budget): JsonResponse
    {
        if ($budget->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $budget->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Budget deleted successfully'
        ]);
    }
    
    private function calculateBudgetDates($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'daily':
                return [
                    'start' => $now->startOfDay()->toDateString(),
                    'end' => $now->endOfDay()->toDateString()
                ];
            case 'weekly':
                return [
                    'start' => $now->startOfWeek()->toDateString(),
                    'end' => $now->endOfWeek()->toDateString()
                ];
            case 'monthly':
                return [
                    'start' => $now->startOfMonth()->toDateString(),
                    'end' => $now->endOfMonth()->toDateString()
                ];
            case 'yearly':
                return [
                    'start' => $now->startOfYear()->toDateString(),
                    'end' => $now->endOfYear()->toDateString()
                ];
            default:
                return [
                    'start' => $now->startOfMonth()->toDateString(),
                    'end' => $now->endOfMonth()->toDateString()
                ];
        }
    }
}