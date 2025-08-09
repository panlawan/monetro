<?php
// app/Policies/UserPreferencePolicy.php

namespace App\Policies;

use App\Models\UserPreference;
use App\Models\User;

class UserPreferencePolicy
{
    /**
     * Determine if user can view any user preferences
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the user preference
     */
    public function view(User $user, UserPreference $preference): bool
    {
        return $user->is_active && $user->id === $preference->user_id;
    }

    /**
     * Determine if user can create user preferences
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the user preference
     */
    public function update(User $user, UserPreference $preference): bool
    {
        return $user->is_active && 
               $user->id === $preference->user_id;
    }

    /**
     * Determine if user can delete the user preference
     */
    public function delete(User $user, UserPreference $preference): bool
    {
        return $user->is_active && 
               $user->id === $preference->user_id;
    }

    /**
     * Determine if user can restore the user preference
     */
    public function restore(User $user, UserPreference $preference): bool
    {
        return $user->is_active && 
               $user->id === $preference->user_id;
    }

    /**
     * Determine if user can force delete the user preference
     */
    public function forceDelete(User $user, UserPreference $preference): bool
    {
        return $user->is_active && 
               $user->id === $preference->user_id;
    }

    /**
     * Determine if user can reset preferences to default
     */
    public function resetToDefault(User $user, UserPreference $preference): bool
    {
        return $this->update($user, $preference);
    }

    /**
     * Determine if user can export preferences
     */
    public function export(User $user, UserPreference $preference): bool
    {
        return $this->view($user, $preference);
    }

    /**
     * Determine if user can import preferences
     */
    public function import(User $user, UserPreference $preference): bool
    {
        return $this->update($user, $preference);
    }
}