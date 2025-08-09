<?php
// app/Models/BudgetPlan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class BudgetPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'period_type',
        'start_date',
        'end_date',
        'total_budget',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_budget' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Budget plan belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Budget plan's categories
     */
    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get period type label
     */
    public function getPeriodTypeLabelAttribute(): string
    {
        return match($this->period_type) {
            'monthly' => 'รายเดือน',
            'quarterly' => 'รายไตรมาส',
            'yearly' => 'รายปี',
            default => $this->period_type,
        };
    }

    /**
     * Get formatted total budget
     */
    public function getFormattedTotalBudgetAttribute(): string
    {
        return number_format($this->total_budget, 2);
    }

    /**
     * Get days remaining
     */
    public function getDaysRemainingAttribute(): int
    {
        return max(0, $this->end_date->diffInDays(now(), false));
    }

    /**
     * Get days elapsed
     */
    public function getDaysElapsedAttribute(): int
    {
        return max(0, now()->diffInDays($this->start_date, false));
    }

    /**
     * Get total days in period
     */
    public function getTotalDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_days <= 0) return 100;
        
        return min(100, ($this->days_elapsed / $this->total_days) * 100);
    }

    /**
     * Check if budget is current
     */
    public function getIsCurrentAttribute(): bool
    {
        $now = now()->toDateString();
        return $now >= $this->start_date->toDateString() && 
               $now <= $this->end_date->toDateString();
    }

    /**
     * Check if budget is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return now()->toDateString() > $this->end_date->toDateString();
    }

    /**
     * Check if budget is future
     */
    public function getIsFutureAttribute(): bool
    {
        return now()->toDateString() < $this->start_date->toDateString();
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Get total allocated amount
     */
    public function getTotalAllocated(): float
    {
        return $this->budgetCategories()->sum('allocated_amount');
    }

    /**
     * Get total spent amount
     */
    public function getTotalSpent(): float
    {
        return $this->budgetCategories()->sum('spent_amount');
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudget(): float
    {
        return $this->total_budget - $this->getTotalSpent();
    }

    /**
     * Get budget utilization percentage
     */
    public function getUtilizationPercentage(): float
    {
        if ($this->total_budget <= 0) return 0;
        
        return min(100, ($this->getTotalSpent() / $this->total_budget) * 100);
    }

    /**
     * Update spent amounts for all categories
     */
    public function updateSpentAmounts(): void
    {
        foreach ($this->budgetCategories as $budgetCategory) {
            $spent = $this->calculateCategorySpent($budgetCategory->category_id);
            $budgetCategory->update([
                'spent_amount' => $spent,
                'remaining_amount' => $budgetCategory->allocated_amount - $spent,
            ]);
        }
    }

    /**
     * Calculate actual spent amount for a category
     */
    private function calculateCategorySpent(int $categoryId): float
    {
        return Transaction::where('user_id', $this->user_id)
                         ->where('category_id', $categoryId)
                         ->where('type', 'expense')
                         ->whereBetween('transaction_date', [
                             $this->start_date,
                             $this->end_date
                         ])
                         ->sum('amount');
    }

    /**
     * Get budget status
     */
    public function getStatus(): array
    {
        $utilization = $this->getUtilizationPercentage();
        
        if ($utilization >= 100) {
            $status = 'exceeded';
            $label = 'เกินงบประมาณ';
            $color = 'red';
        } elseif ($utilization >= 80) {
            $status = 'warning';
            $label = 'ใกล้เกินงบประมาณ';
            $color = 'yellow';
        } else {
            $status = 'good';
            $label = 'อยู่ในงบประมาณ';
            $color = 'green';
        }

        return [
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'utilization' => $utilization,
        ];
    }

    /**
     * Clone budget plan for next period
     */
    public function cloneForNextPeriod(): self
    {
        $nextStartDate = match($this->period_type) {
            'monthly' => $this->start_date->addMonth(),
            'quarterly' => $this->start_date->addMonths(3),
            'yearly' => $this->start_date->addYear(),
            default => $this->start_date->addMonth(),
        };

        $nextEndDate = match($this->period_type) {
            'monthly' => $nextStartDate->copy()->endOfMonth(),
            'quarterly' => $nextStartDate->copy()->addMonths(3)->subDay(),
            'yearly' => $nextStartDate->copy()->addYear()->subDay(),
            default => $nextStartDate->copy()->endOfMonth(),
        };

        $newPlan = $this->replicate();
        $newPlan->name = $this->name . ' (ต่อ)';
        $newPlan->start_date = $nextStartDate;
        $newPlan->end_date = $nextEndDate;
        $newPlan->save();

        // Clone budget categories
        foreach ($this->budgetCategories as $budgetCategory) {
            $newBudgetCategory = $budgetCategory->replicate();
            $newBudgetCategory->budget_plan_id = $newPlan->id;
            $newBudgetCategory->spent_amount = 0;
            $newBudgetCategory->remaining_amount = $newBudgetCategory->allocated_amount;
            $newBudgetCategory->save();
        }

        return $newPlan;
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for active budget plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current budget plans
     */
    public function scopeCurrent($query)
    {
        $now = now()->toDateString();
        return $query->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }

    /**
     * Scope by period type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('period_type', $type);
    }

    /**
     * Scope ordered by date (newest first)
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('start_date', 'desc');
    }
}