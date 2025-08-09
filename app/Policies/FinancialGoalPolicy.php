<?php
// app/Policies/FinancialGoalPolicy.php

namespace App\Policies;

use App\Models\FinancialGoal;
use App\Models\User;

class FinancialGoalPolicy
{
    /**
     * Determine if user can view any financial goals
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the financial goal
     */
    public function view(User $user, FinancialGoal $goal): bool
    {
        return $user->is_active && $user->id === $goal->user_id;
    }

    /**
     * Determine if user can create financial goals
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the financial goal
     */
    public function update(User $user, FinancialGoal $goal): bool
    {
        return $user->is_active && 
               $user->id === $goal->user_id;
    }

    /**
     * Determine if user can delete the financial goal
     */
    public function delete(User $user, FinancialGoal $goal): bool
    {
        return $user->is_active && 
               $user->id === $goal->user_id;
    }

    /**
     * Determine if user can restore the financial goal
     */
    public function restore(User $user, FinancialGoal $goal): bool
    {
        return $user->is_active && 
               $user->id === $goal->user_id;
    }

    /**
     * Determine if user can force delete the financial goal
     */
    public function forceDelete(User $user, FinancialGoal $goal): bool
    {
        return $user->is_active && 
               $user->id === $goal->user_id;
    }

    /**
     * Determine if user can add contributions to goal
     */
    public function addContribution(User $user, FinancialGoal $goal): bool
    {
        return $user->is_active && 
               $user->id === $goal->user_id &&
               in_array($goal->status, ['planning', 'in_progress']);
    }

    /**
     * Determine if user can change goal status
     */
    public function changeStatus(User $user, FinancialGoal $goal): bool
    {
        return $this->update($user, $goal);
    }

    /**
     * Determine if user can link account to goal
     */
    public function linkAccount(User $user, FinancialGoal $goal): bool
    {
        return $this->update($user, $goal);
    }

    /**
     * Determine if user can view goal progress
     */
    public function viewProgress(User $user, FinancialGoal $goal): bool
    {
        return $this->view($user, $goal);
    }
}