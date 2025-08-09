<?php
// app/Policies/BudgetCategoryPolicy.php

namespace App\Policies;

use App\Models\BudgetCategory;
use App\Models\User;

class BudgetCategoryPolicy
{
    /**
     * Determine if user can view any budget categories
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the budget category
     */
    public function view(User $user, BudgetCategory $budgetCategory): bool
    {
        return $user->is_active && 
               $user->id === $budgetCategory->budgetPlan->user_id;
    }

    /**
     * Determine if user can create budget categories
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the budget category
     */
    public function update(User $user, BudgetCategory $budgetCategory): bool
    {
        return $user->is_active && 
               $user->id === $budgetCategory->budgetPlan->user_id;
    }

    /**
     * Determine if user can delete the budget category
     */
    public function delete(User $user, BudgetCategory $budgetCategory): bool
    {
        return $user->is_active && 
               $user->id === $budgetCategory->budgetPlan->user_id;
    }

    /**
     * Determine if user can restore the budget category
     */
    public function restore(User $user, BudgetCategory $budgetCategory): bool
    {
        return $user->is_active && 
               $user->id === $budgetCategory->budgetPlan->user_id;
    }

    /**
     * Determine if user can force delete the budget category
     */
    public function forceDelete(User $user, BudgetCategory $budgetCategory): bool
    {
        return $user->is_active && 
               $user->id === $budgetCategory->budgetPlan->user_id;
    }

    /**
     * Determine if user can adjust budget allocation
     */
    public function adjustAllocation(User $user, BudgetCategory $budgetCategory): bool
    {
        return $this->update($user, $budgetCategory) &&
               $budgetCategory->is_flexible;
    }
}