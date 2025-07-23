<?php
// app/Http/Controllers/TransactionController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'type' => 'required|in:income,expense',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify category belongs to user and matches type
        $category = Category::where('id', $request->category_id)
                          ->where('user_id', auth()->id())
                          ->where('type', $request->type)
                          ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid category selected.'
            ], 422);
        }

        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'type' => $request->type,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction added successfully!',
            'transaction' => $transaction->load('category')
        ]);
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        // Check if user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify category belongs to user
        $category = Category::where('id', $request->category_id)
                          ->where('user_id', auth()->id())
                          ->where('type', $transaction->type)
                          ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid category selected.'
            ], 422);
        }

        $transaction->update([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully!',
            'transaction' => $transaction->load('category')
        ]);
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        // Check if user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully!'
        ]);
    }

    /**
     * Get transaction data for editing
     */
    public function show(Transaction $transaction): JsonResponse
    {
        // Check if user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'transaction' => $transaction->load('category')
        ]);
    }
}