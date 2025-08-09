<?php
// app/Policies/TransactionPolicy.php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine if user can view any transactions
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the transaction
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->is_active && $user->id === $transaction->user_id;
    }

    /**
     * Determine if user can create transactions
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the transaction
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->is_active && 
               $user->id === $transaction->user_id &&
               $transaction->canBeEdited();
    }

    /**
     * Determine if user can delete the transaction
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->is_active && 
               $user->id === $transaction->user_id &&
               $transaction->canBeDeleted();
    }

    /**
     * Determine if user can restore the transaction
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->is_active && 
               $user->id === $transaction->user_id;
    }

    /**
     * Determine if user can force delete the transaction
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->is_active && 
               $user->id === $transaction->user_id &&
               $transaction->canBeDeleted();
    }

    /**
     * Determine if user can add attachments to transaction
     */
    public function addAttachment(User $user, Transaction $transaction): bool
    {
        return $this->update($user, $transaction);
    }

    /**
     * Determine if user can remove attachments from transaction
     */
    public function removeAttachment(User $user, Transaction $transaction): bool
    {
        return $this->update($user, $transaction);
    }

    /**
     * Determine if user can add tags to transaction
     */
    public function addTags(User $user, Transaction $transaction): bool
    {
        return $this->update($user, $transaction);
    }

    /**
     * Determine if user can remove tags from transaction
     */
    public function removeTags(User $user, Transaction $transaction): bool
    {
        return $this->update($user, $transaction);
    }

    /**
     * Determine if user can duplicate transaction
     */
    public function duplicate(User $user, Transaction $transaction): bool
    {
        return $user->is_active && 
               $user->id === $transaction->user_id;
    }

    /**
     * Determine if user can export transactions
     */
    public function export(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can import transactions
     */
    public function import(User $user): bool
    {
        return $user->is_active;
    }
}