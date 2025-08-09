<?php
// app/Policies/BudgetPlanPolicy.php

namespace App\Policies;

use App\Models\BudgetPlan;
use App\Models\User;

class BudgetPlanPolicy
{
    /**
     * Determine if user can view any budget plans
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the budget plan
     */
    public function view(User $user, BudgetPlan $budgetPlan): bool
    {
        return $user->is_active && $user->id === $budgetPlan->user_id;
    }

    /**
     * Determine if user can create budget plans
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the budget plan
     */
    public function update(User $user, BudgetPlan $budgetPlan): bool
    {
        return $user->is_active && 
               $user->id === $budgetPlan->user_id;
    }

    /**
     * Determine if user can delete the budget plan
     */
    public function delete(User $user, BudgetPlan $budgetPlan): bool
    {
        return $user->is_active && 
               $user->id === $budgetPlan->user_id;
    }

    /**
     * Determine if user can restore the budget plan
     */
    public function restore(User $user, BudgetPlan $budgetPlan): bool
    {
        return $user->is_active && 
               $user->id === $budgetPlan->user_id;
    }

    /**
     * Determine if user can force delete the budget plan
     */
    public function forceDelete(User $user, BudgetPlan $budgetPlan): bool
    {
        return $user->is_active && 
               $user->id === $budgetPlan->user_id;
    }

    /**
     * Determine if user can clone budget plan
     */
    public function clone(User $user, BudgetPlan $budgetPlan): bool
    {
        return $user->is_active && 
               $user->id === $budgetPlan->user_id;
    }

    /**
     * Determine if user can manage budget categories
     */
    public function manageBudgetCategories(User $user, BudgetPlan $budgetPlan): bool
    {
        return $this->update($user, $budgetPlan);
    }

    /**
     * Determine if user can view budget reports
     */
    public function viewReports(User $user, BudgetPlan $budgetPlan): bool
    {
        return $this->view($user, $budgetPlan);
    }

    /**
     * Determine if user can activate/deactivate budget plan
     */
    public function toggleStatus(User $user, BudgetPlan $budgetPlan): bool
    {
        return $this->update($user, $budgetPlan);
    }
}