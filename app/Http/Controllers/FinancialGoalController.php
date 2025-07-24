<?php

namespace App\Http\Controllers;

use App\Models\FinancialGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialGoalController extends Controller
{
    // GET /financial-goals
    public function index()
    {
        $goals = FinancialGoal::where('user_id', Auth::id())->get();
        return response()->json($goals);
    }

    // POST /financial-goals
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'nullable|numeric|min:0',
            'target_date' => 'required|date|after:today',
            'type' => 'required|in:savings,investment,debt_payoff',
            'monthly_contribution' => 'nullable|numeric|min:0',
            'auto_calculate' => 'boolean',
        ]);

        $goal = FinancialGoal::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'status' => 'in_progress',
        ]));

        return response()->json($goal, 201);
    }

    // GET /financial-goals/{id}
    public function show($id)
    {
        $goal = FinancialGoal::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

        return response()->json($goal);
    }

    // PUT/PATCH /financial-goals/{id}
    public function update(Request $request, $id)
    {
        $goal = FinancialGoal::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'sometimes|numeric|min:0',
            'current_amount' => 'sometimes|numeric|min:0',
            'target_date' => 'sometimes|date|after:today',
            'type' => 'sometimes|in:savings,investment,debt_payoff',
            'monthly_contribution' => 'nullable|numeric|min:0',
            'auto_calculate' => 'boolean',
            'status' => 'in:in_progress,completed,cancelled'
        ]);

        $goal->update($validated);
        return response()->json($goal);
    }

    // DELETE /financial-goals/{id}
    public function destroy($id)
    {
        $goal = FinancialGoal::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

        $goal->delete();
        return response()->json(['message' => 'Goal deleted.']);
    }
}
