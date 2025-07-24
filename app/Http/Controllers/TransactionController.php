<?php
// app/Http/Controllers/TransactionController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Store a newly created transaction
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date',
            'type' => 'required|in:income,expense',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_interval' => 'nullable|integer|min:1|max:365',
            'recurring_end_date' => 'nullable|date|after:transaction_date',
        ]);

        // Check if category belongs to user
        $category = Category::where('id', $validated['category_id'])
                          ->where('user_id', auth()->id())
                          ->first();

        if (!$category) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid category'], 422);
            }
            return back()->withErrors(['category_id' => 'Invalid category selected']);
        }

        // Validate type matches category type
        if ($category->type !== $validated['type']) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Transaction type does not match category type'], 422);
            }
            return back()->withErrors(['type' => 'Transaction type does not match category type']);
        }

        $validated['user_id'] = auth()->id();

        $transaction = Transaction::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction added successfully',
                'transaction' => $transaction->load('category')
            ]);
        }

        return redirect()->route('finance.transactions')
                        ->with('success', 'Transaction added successfully');
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction): View|JsonResponse
    {
        // Check if transaction belongs to authenticated user
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->load('category');

        if (request()->expectsJson()) {
            return response()->json($transaction);
        }

        return view('finance.transactions.show', compact('transaction'));
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, Transaction $transaction): JsonResponse|RedirectResponse
    {
        // Check if transaction belongs to authenticated user
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date',
            'type' => 'required|in:income,expense',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_interval' => 'nullable|integer|min:1|max:365',
            'recurring_end_date' => 'nullable|date|after:transaction_date',
        ]);

        // Check if category belongs to user
        $category = Category::where('id', $validated['category_id'])
                          ->where('user_id', auth()->id())
                          ->first();

        if (!$category) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid category'], 422);
            }
            return back()->withErrors(['category_id' => 'Invalid category selected']);
        }

        // Validate type matches category type
        if ($category->type !== $validated['type']) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Transaction type does not match category type'], 422);
            }
            return back()->withErrors(['type' => 'Transaction type does not match category type']);
        }

        $transaction->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'transaction' => $transaction->load('category')
            ]);
        }

        return redirect()->route('finance.transactions')
                        ->with('success', 'Transaction updated successfully');
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction): JsonResponse|RedirectResponse
    {
        // Check if transaction belongs to authenticated user
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
        }

        return redirect()->route('finance.transactions')
                        ->with('success', 'Transaction deleted successfully');
    }
}