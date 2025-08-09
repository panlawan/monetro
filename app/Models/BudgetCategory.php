<?php
// app/Models/BudgetCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_plan_id',
        'category_id',
        'allocated_amount',
        'spent_amount',
        'remaining_amount',
        'is_flexible',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'is_flexible' => 'boolean',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Budget category belongs to budget plan
     */
    public function budgetPlan(): BelongsTo
    {
        return $this->belongsTo(BudgetPlan::class);
    }

    /**
     * Budget category belongs to category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get formatted allocated amount
     */
    public function getFormattedAllocatedAttribute(): string
    {
        return number_format($this->allocated_amount, 2);
    }

    /**
     * Get formatted spent amount
     */
    public function getFormattedSpentAttribute(): string
    {
        return number_format($this->spent_amount, 2);
    }

    /**
     * Get formatted remaining amount
     */
    public function getFormattedRemainingAttribute(): string
    {
        return number_format($this->remaining_amount, 2);
    }

    /**
     * Get utilization percentage
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->allocated_amount <= 0) return 0;
        
        return min(100, ($this->spent_amount / $this->allocated_amount) * 100);
    }

    /**
     * Check if over budget
     */
    public function getIsOverBudgetAttribute(): bool
    {
        return $this->spent_amount > $this->allocated_amount;
    }

    /**
     * Check if near budget limit (80%+)
     */
    public function getIsNearLimitAttribute(): bool
    {
        return $this->utilization_percentage >= 80 && !$this->is_over_budget;
    }

    /**
     * Get status with color
     */
    public function getStatusAttribute(): array
    {
        if ($this->is_over_budget) {
            return [
                'status' => 'exceeded',
                'label' => 'เกินงบประมาณ',
                'color' => 'red',
                'css_class' => 'text-red-600 bg-red-100',
            ];
        } elseif ($this->is_near_limit) {
            return [
                'status' => 'warning',
                'label' => 'ใกล้เกินงบประมาณ',
                'color' => 'yellow',
                'css_class' => 'text-yellow-600 bg-yellow-100',
            ];
        } else {
            return [
                'status' => 'good',
                'label' => 'อยู่ในงบประมาณ',
                'color' => 'green',
                'css_class' => 'text-green-600 bg-green-100',
            ];
        }
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Update spent amount from actual transactions
     */
    public function updateSpentAmount(): void
    {
        $spent = Transaction::where('user_id', $this->budgetPlan->user_id)
                           ->where('category_id', $this->category_id)
                           ->where('type', 'expense')
                           ->whereBetween('transaction_date', [
                               $this->budgetPlan->start_date,
                               $this->budgetPlan->end_date
                           ])
                           ->sum('amount');

        $this->update([
            'spent_amount' => $spent,
            'remaining_amount' => $this->allocated_amount - $spent,
        ]);
    }

    /**
     * Get daily average spending
     */
    public function getDailyAverageSpending(): float
    {
        $daysElapsed = max(1, $this->budgetPlan->days_elapsed);
        return $this->spent_amount / $daysElapsed;
    }

    /**
     * Get projected spending for full period
     */
    public function getProjectedSpending(): float
    {
        $dailyAverage = $this->getDailyAverageSpending();
        return $dailyAverage * $this->budgetPlan->total_days;
    }

    /**
     * Check if on track with budget
     */
    public function isOnTrack(): bool
    {
        $expectedSpent = ($this->allocated_amount / $this->budgetPlan->total_days) * $this->budgetPlan->days_elapsed;
        return $this->spent_amount <= $expectedSpent * 1.1; // 10% tolerance
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for over budget categories
     */
    public function scopeOverBudget($query)
    {
        return $query->whereRaw('spent_amount > allocated_amount');
    }

    /**
     * Scope for categories near limit
     */
    public function scopeNearLimit($query)
    {
        return $query->whereRaw('spent_amount >= allocated_amount * 0.8')
                    ->whereRaw('spent_amount <= allocated_amount');
    }

    /**
     * Scope for flexible categories
     */
    public function scopeFlexible($query)
    {
        return $query->where('is_flexible', true);
    }

    /**
     * Scope ordered by utilization (highest first)
     */
    public function scopeByUtilization($query)
    {
        return $query->orderByRaw('(spent_amount / allocated_amount) DESC');
    }
}