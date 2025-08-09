<?php
// app/Policies/AccountPolicy.php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    /**
     * Determine if user can view any accounts
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the account
     */
    public function view(User $user, Account $account): bool
    {
        return $user->is_active && $user->id === $account->user_id;
    }

    /**
     * Determine if user can create accounts
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the account
     */
    public function update(User $user, Account $account): bool
    {
        return $user->is_active && 
               $user->id === $account->user_id;
    }

    /**
     * Determine if user can delete the account
     */
    public function delete(User $user, Account $account): bool
    {
        return $user->is_active && 
               $user->id === $account->user_id &&
               $account->canBeDeleted();
    }

    /**
     * Determine if user can restore the account
     */
    public function restore(User $user, Account $account): bool
    {
        return $user->is_active && 
               $user->id === $account->user_id;
    }

    /**
     * Determine if user can force delete the account
     */
    public function forceDelete(User $user, Account $account): bool
    {
        // Only allow admin or account owner with special permission
        return $user->is_active && 
               $user->id === $account->user_id &&
               $account->canBeDeleted();
    }

    /**
     * Determine if user can view account balance
     */
    public function viewBalance(User $user, Account $account): bool
    {
        return $this->view($user, $account);
    }

    /**
     * Determine if user can update account balance manually
     */
    public function updateBalance(User $user, Account $account): bool
    {
        return $user->is_active && 
               $user->id === $account->user_id &&
               $account->is_active;
    }
}