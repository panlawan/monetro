<?php
// app/Policies/TransactionTagPolicy.php

namespace App\Policies;

use App\Models\TransactionTag;
use App\Models\User;

class TransactionTagPolicy
{
    /**
     * Determine if user can view any transaction tags
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the transaction tag
     */
    public function view(User $user, TransactionTag $tag): bool
    {
        return $user->is_active && $user->id === $tag->user_id;
    }

    /**
     * Determine if user can create transaction tags
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the transaction tag
     */
    public function update(User $user, TransactionTag $tag): bool
    {
        return $user->is_active && 
               $user->id === $tag->user_id;
    }

    /**
     * Determine if user can delete the transaction tag
     */
    public function delete(User $user, TransactionTag $tag): bool
    {
        return $user->is_active && 
               $user->id === $tag->user_id;
    }

    /**
     * Determine if user can restore the transaction tag
     */
    public function restore(User $user, TransactionTag $tag): bool
    {
        return $user->is_active && 
               $user->id === $tag->user_id;
    }

    /**
     * Determine if user can force delete the transaction tag
     */
    public function forceDelete(User $user, TransactionTag $tag): bool
    {
        return $user->is_active && 
               $user->id === $tag->user_id;
    }

    /**
     * Determine if user can merge tags
     */
    public function merge(User $user, TransactionTag $sourceTag, TransactionTag $targetTag): bool
    {
        return $user->is_active && 
               $user->id === $sourceTag->user_id &&
               $user->id === $targetTag->user_id;
    }
}