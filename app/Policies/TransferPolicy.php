<?php
// app/Policies/TransferPolicy.php

namespace App\Policies;

use App\Models\Transfer;
use App\Models\User;

class TransferPolicy
{
    /**
     * Determine if user can view any transfers
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the transfer
     */
    public function view(User $user, Transfer $transfer): bool
    {
        return $user->is_active && $user->id === $transfer->user_id;
    }

    /**
     * Determine if user can create transfers
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the transfer
     */
    public function update(User $user, Transfer $transfer): bool
    {
        return $user->is_active && 
               $user->id === $transfer->user_id;
    }

    /**
     * Determine if user can delete the transfer
     */
    public function delete(User $user, Transfer $transfer): bool
    {
        return $user->is_active && 
               $user->id === $transfer->user_id;
    }

    /**
     * Determine if user can restore the transfer
     */
    public function restore(User $user, Transfer $transfer): bool
    {
        return $user->is_active && 
               $user->id === $transfer->user_id;
    }

    /**
     * Determine if user can force delete the transfer
     */
    public function forceDelete(User $user, Transfer $transfer): bool
    {
        return $user->is_active && 
               $user->id === $transfer->user_id;
    }

    /**
     * Determine if user can reverse the transfer
     */
    public function reverse(User $user, Transfer $transfer): bool
    {
        return $user->is_active && 
               $user->id === $transfer->user_id;
    }
}