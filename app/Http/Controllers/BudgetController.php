<?php
// app/Http/Controllers/BudgetController.php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created budget
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify category belongs to user and is expense type
        $category = Category::where('id', $request->category_id)
                          ->where('user_id', auth()->id())
                          ->where('type', 'expense')
                          ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid category selected.'
            ], 422);
        }

        // Check if budget already exists
        $existingBudget = Budget::where('user_id', auth()->id())
                               ->where('category_id', $request->category_id)
                               ->where('month', $request->month)
                               ->where('year', $request->year)
                               ->first();

        if ($existingBudget) {
            return response()->json([
                'success' => false,
                'message' => 'Budget for this category and month already exists.'
            ], 422);
        }

        $budget = Budget::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'month' => $request->month,
            'year' => $request->year,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget created successfully!',
            'budget' => $budget->load('category')
        ]);
    }

    /**
     * Update the specified budget
     */
    public function update(Request $request, Budget $budget): JsonResponse
    {
        // Check if user owns this budget
        if ($budget->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $budget->update([
            'amount' => $request->amount,
            'notes' => $request->notes,
            'is_active' => $request->get('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget updated successfully!',
            'budget' => $budget->load('category')
        ]);
    }

    /**
     * Remove the specified budget
     */
    public function destroy(Budget $budget): JsonResponse
    {
        // Check if user owns this budget
        if ($budget->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $budget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget deleted successfully!'
        ]);
    }
}