<?php
// app/Policies/MonthlySummaryPolicy.php

namespace App\Policies;

use App\Models\MonthlySummary;
use App\Models\User;

class MonthlySummaryPolicy
{
    /**
     * Determine if user can view any monthly summaries
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view the monthly summary
     */
    public function view(User $user, MonthlySummary $summary): bool
    {
        return $user->is_active && $user->id === $summary->user_id;
    }

    /**
     * Determine if user can create monthly summaries
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can update the monthly summary
     */
    public function update(User $user, MonthlySummary $summary): bool
    {
        return $user->is_active && 
               $user->id === $summary->user_id;
    }

    /**
     * Determine if user can delete the monthly summary
     */
    public function delete(User $user, MonthlySummary $summary): bool
    {
        return $user->is_active && 
               $user->id === $summary->user_id;
    }

    /**
     * Determine if user can restore the monthly summary
     */
    public function restore(User $user, MonthlySummary $summary): bool
    {
        return $user->is_active && 
               $user->id === $summary->user_id;
    }

    /**
     * Determine if user can force delete the monthly summary
     */
    public function forceDelete(User $user, MonthlySummary $summary): bool
    {
        return $user->is_active && 
               $user->id === $summary->user_id;
    }

    /**
     * Determine if user can recalculate summary
     */
    public function recalculate(User $user, MonthlySummary $summary): bool
    {
        return $user->is_active && 
               $user->id === $summary->user_id;
    }

    /**
     * Determine if user can export summary
     */
    public function export(User $user, MonthlySummary $summary): bool
    {
        return $this->view($user, $summary);
    }

    /**
     * Determine if user can view detailed breakdown
     */
    public function viewBreakdown(User $user, MonthlySummary $summary): bool
    {
        return $this->view($user, $summary);
    }

    /**
     * Determine if user can compare with other periods
     */
    public function compare(User $user, MonthlySummary $summary): bool
    {
        return $this->view($user, $summary);
    }

    /**
     * Determine if user can generate summaries for date range
     */
    public function generateRange(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can view charts and analytics
     */
    public function viewAnalytics(User $user, MonthlySummary $summary): bool
    {
        return $this->view($user, $summary);
    }

    /**
     * Determine if user can bulk generate summaries
     */
    public function bulkGenerate(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if user can regenerate all summaries
     */
    public function regenerateAll(User $user): bool
    {
        return $user->is_active;
    }
}