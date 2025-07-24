<?php
// app/Http/Controllers/GoalController.php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class GoalController extends Controller
{
    /**
     * Store a newly created goal
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'target_amount' => 'required|numeric|min:0.01|max:999999999.99',
            'current_amount' => 'nullable|numeric|min:0|max:999999999.99',
            'target_date' => 'required|date|after:today',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50'
        ]);

        $validated['user_id'] = auth()->id();
        $validated['current_amount'] = $validated['current_amount'] ?? 0;
        $validated['status'] = 'active';

        $goal = Goal::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Goal created successfully',
                'goal' => $goal
            ]);
        }

        return redirect()->route('finance.goals')
                        ->with('success', 'Goal created successfully');
    }

    /**
     * Display the specified goal
     */
    public function show(Goal $goal): JsonResponse
    {
        // Check if goal belongs to authenticated user
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        return response()->json($goal);
    }

    /**
     * Update the specified goal
     */
    public function update(Request $request, Goal $goal): JsonResponse|RedirectResponse
    {
        // Check if goal belongs to authenticated user
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'target_amount' => 'required|numeric|min:0.01|max:999999999.99',
            'current_amount' => 'nullable|numeric|min:0|max:999999999.99',
            'target_date' => 'required|date',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
            'status' => 'required|in:active,completed,paused,cancelled'
        ]);

        $goal->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Goal updated successfully',
                'goal' => $goal
            ]);
        }

        return redirect()->route('finance.goals')
                        ->with('success', 'Goal updated successfully');
    }

    /**
     * Remove the specified goal
     */
    public function destroy(Goal $goal): JsonResponse|RedirectResponse
    {
        // Check if goal belongs to authenticated user
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $goal->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Goal deleted successfully'
            ]);
        }

        return redirect()->route('finance.goals')
                        ->with('success', 'Goal deleted successfully');
    }

    /**
     * Add progress to a goal
     */
    public function addProgress(Request $request, Goal $goal): JsonResponse|RedirectResponse
    {
        // Check if goal belongs to authenticated user
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'note' => 'nullable|string|max:255'
        ]);

        $success = $goal->addProgress($validated['amount']);

        if (!$success) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to add progress'], 422);
            }
            return back()->withErrors(['amount' => 'Failed to add progress']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Progress added successfully',
                'goal' => $goal->fresh()
            ]);
        }

        return redirect()->route('finance.goals')
                        ->with('success', 'Progress added successfully');
    }

    /**
     * Mark goal as completed
     */
    public function markCompleted(Goal $goal): JsonResponse|RedirectResponse
    {
        // Check if goal belongs to authenticated user
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $success = $goal->markAsCompleted();

        if (!$success) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Failed to mark goal as completed'], 422);
            }
            return back()->withErrors(['goal' => 'Failed to mark goal as completed']);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Goal marked as completed',
                'goal' => $goal->fresh()
            ]);
        }

        return redirect()->route('finance.goals')
                        ->with('success', 'Goal marked as completed');
    }
}