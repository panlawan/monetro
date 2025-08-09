<?php
// app/Policies/CategoryPolicy.php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Determine if user can view any categories
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the category
     */
    public function view(User $user, Category $category): bool
    {
        return $user->is_active && $user->id === $category->user_id;
    }

    /**
     * Determine if user can create categories
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the category
     */
    public function update(User $user, Category $category): bool
    {
        return $user->is_active && 
               $user->id === $category->user_id;
    }

    /**
     * Determine if user can delete the category
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->is_active && 
               $user->id === $category->user_id &&
               $category->canBeDeleted();
    }

    /**
     * Determine if user can restore the category
     */
    public function restore(User $user, Category $category): bool
    {
        return $user->is_active && 
               $user->id === $category->user_id;
    }

    /**
     * Determine if user can force delete the category
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return $user->is_active && 
               $user->id === $category->user_id &&
               $category->canBeDeleted();
    }

    /**
     * Determine if user can reorder categories
     */
    public function reorder(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view category statistics
     */
    public function viewStats(User $user, Category $category): bool
    {
        return $this->view($user, $category);
    }
}