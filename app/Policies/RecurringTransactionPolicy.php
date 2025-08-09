<?php
// app/Policies/RecurringTransactionPolicy.php

namespace App\Policies;

use App\Models\RecurringTransaction;
use App\Models\User;

class RecurringTransactionPolicy
{
    /**
     * Determine if user can view any recurring transactions
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the recurring transaction
     */
    public function view(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->is_active && $user->id === $recurringTransaction->user_id;
    }

    /**
     * Determine if user can create recurring transactions
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the recurring transaction
     */
    public function update(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->is_active && 
               $user->id === $recurringTransaction->user_id;
    }

    /**
     * Determine if user can delete the recurring transaction
     */
    public function delete(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->is_active && 
               $user->id === $recurringTransaction->user_id;
    }

    /**
     * Determine if user can restore the recurring transaction
     */
    public function restore(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->is_active && 
               $user->id === $recurringTransaction->user_id;
    }

    /**
     * Determine if user can force delete the recurring transaction
     */
    public function forceDelete(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->is_active && 
               $user->id === $recurringTransaction->user_id;
    }

    /**
     * Determine if user can generate transaction manually
     */
    public function generateTransaction(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->is_active && 
               $user->id === $recurringTransaction->user_id &&
               $recurringTransaction->is_active;
    }

    /**
     * Determine if user can pause/resume recurring transaction
     */
    public function toggleStatus(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $this->update($user, $recurringTransaction);
    }

    /**
     * Determine if user can skip next occurrence
     */
    public function skipNext(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $this->update($user, $recurringTransaction);
    }

    /**
     * Determine if user can view generated transactions
     */
    public function viewGeneratedTransactions(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $this->view($user, $recurringTransaction);
    }
}