<?php
// app/Policies/TransactionAttachmentPolicy.php

namespace App\Policies;

use App\Models\TransactionAttachment;
use App\Models\User;

class TransactionAttachmentPolicy
{
    /**
     * Determine if user can view any transaction attachments
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the transaction attachment
     */
    public function view(User $user, TransactionAttachment $attachment): bool
    {
        return $user->is_active && 
               $user->id === $attachment->transaction->user_id;
    }

    /**
     * Determine if user can create transaction attachments
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the transaction attachment
     */
    public function update(User $user, TransactionAttachment $attachment): bool
    {
        return $user->is_active && 
               $user->id === $attachment->transaction->user_id;
    }

    /**
     * Determine if user can delete the transaction attachment
     */
    public function delete(User $user, TransactionAttachment $attachment): bool
    {
        return $user->is_active && 
               $user->id === $attachment->transaction->user_id;
    }

    /**
     * Determine if user can restore the transaction attachment
     */
    public function restore(User $user, TransactionAttachment $attachment): bool
    {
        return $user->is_active && 
               $user->id === $attachment->transaction->user_id;
    }

    /**
     * Determine if user can force delete the transaction attachment
     */
    public function forceDelete(User $user, TransactionAttachment $attachment): bool
    {
        return $user->is_active && 
               $user->id === $attachment->transaction->user_id;
    }

    /**
     * Determine if user can download the attachment
     */
    public function download(User $user, TransactionAttachment $attachment): bool
    {
        return $this->view($user, $attachment) && $attachment->fileExists();
    }
}