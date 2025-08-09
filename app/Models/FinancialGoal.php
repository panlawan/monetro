<?php
// app/Models/FinancialGoal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FinancialGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_amount',
        'current_amount',
        'monthly_contribution',
        'target_date',
        'category',
        'status',
        'priority',
        'linked_account_id',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'monthly_contribution' => 'decimal:2',
        'target_date' => 'date',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Goal belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Goal's linked account
     */
    public function linkedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'linked_account_id');
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'emergency' => 'เงินฉุกเฉิน',
            'retirement' => 'เกษียณ',
            'investment' => 'การลงทุน',
            'purchase' => 'การซื้อ',
            'other' => 'อื่นๆ',
            default => $this->category,
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planning' => 'วางแผน',
            'in_progress' => 'กำลังดำเนินการ',
            'achieved' => 'สำเร็จแล้ว',
            'paused' => 'หยุดชั่วคราว',
            default => $this->status,
        };
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low' => 'ต่ำ',
            'medium' => 'ปกติ',
            'high' => 'สูง',
            default => $this->priority,
        };
    }

    /**
     * Get formatted target amount
     */
    public function getFormattedTargetAmountAttribute(): string
    {
        return number_format($this->target_amount, 2);
    }

    /**
     * Get formatted current amount
     */
    public function getFormattedCurrentAmountAttribute(): string
    {
        return number_format($this->current_amount, 2);
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Get formatted remaining amount
     */
    public function getFormattedRemainingAmountAttribute(): string
    {
        return number_format($this->remaining_amount, 2);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount <= 0) return 0;
        
        return min(100, ($this->current_amount / $this->target_amount) * 100);
    }

    /**
     * Check if goal is achieved
     */
    public function getIsAchievedAttribute(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Get days remaining
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->target_date) return null;
        
        return max(0, now()->diffInDays($this->target_date, false));
    }

    /**
     * Check if overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->target_date) return false;
        
        return now()->toDateString() > $this->target_date->toDateString() && !$this->is_achieved;
    }

    /**
     * Get progress status with color
     */
    public function getProgressStatusAttribute(): array
    {
        if ($this->is_achieved) {
            return [
                'status' => 'achieved',
                'label' => 'สำเร็จแล้ว',
                'color' => 'green',
                'css_class' => 'text-green-600 bg-green-100',
            ];
        } elseif ($this->is_overdue) {
            return [
                'status' => 'overdue',
                'label' => 'เลยกำหนด',
                'color' => 'red',
                'css_class' => 'text-red-600 bg-red-100',
            ];
        } elseif ($this->progress_percentage >= 75) {
            return [
                'status' => 'almost_there',
                'label' => 'ใกล้สำเร็จ',
                'color' => 'blue',
                'css_class' => 'text-blue-600 bg-blue-100',
            ];
        } elseif ($this->progress_percentage >= 50) {
            return [
                'status' => 'on_track',
                'label' => 'กำลังดำเนินการ',
                'color' => 'yellow',
                'css_class' => 'text-yellow-600 bg-yellow-100',
            ];
        } else {
            return [
                'status' => 'started',
                'label' => 'เริ่มต้น',
                'color' => 'gray',
                'css_class' => 'text-gray-600 bg-gray-100',
            ];
        }
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Calculate required monthly contribution
     */
    public function getRequiredMonthlyContribution(): ?float
    {
        if (!$this->target_date) return null;
        
        $monthsRemaining = max(1, now()->diffInMonths($this->target_date));
        return $this->remaining_amount / $monthsRemaining;
    }

    /**
     * Calculate months to completion based on current contribution
     */
    public function getMonthsToCompletion(): ?float
    {
        if (!$this->monthly_contribution || $this->monthly_contribution <= 0) {
            return null;
        }
        
        return $this->remaining_amount / $this->monthly_contribution;
    }

    /**
     * Get estimated completion date
     */
    public function getEstimatedCompletionDate(): ?Carbon
    {
        $months = $this->getMonthsToCompletion();
        if (!$months) return null;
        
        return now()->addMonths(ceil($months));
    }

    /**
     * Add contribution to goal
     */
    public function addContribution(float $amount): void
    {
        $this->increment('current_amount', $amount);
        
        // Check if goal is achieved
        if ($this->current_amount >= $this->target_amount && $this->status !== 'achieved') {
            $this->update(['status' => 'achieved']);
        }
    }

    /**
     * Update current amount from linked account balance
     */
    public function updateFromLinkedAccount(): void
    {
        if ($this->linkedAccount) {
            $this->update(['current_amount' => $this->linkedAccount->current_balance]);
        }
    }

    /**
     * Calculate recommended monthly savings
     */
    public function getRecommendedMonthlySavings(): array
    {
        $required = $this->getRequiredMonthlyContribution();
        $current = $this->monthly_contribution ?? 0;
        
        return [
            'required' => $required,
            'current' => $current,
            'difference' => $required ? $required - $current : 0,
            'is_sufficient' => $required ? $current >= $required : true,
        ];
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for active goals
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planning', 'in_progress']);
    }

    /**
     * Scope for achieved goals
     */
    public function scopeAchieved($query)
    {
        return $query->where('status', 'achieved');
    }

    /**
     * Scope by category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by priority
     */
    public function scopeOfPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for overdue goals
     */
    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', now())
                    ->where('status', '!=', 'achieved');
    }

    /**
     * Scope ordered by priority and target date
     */
    public function scopeByImportance($query)
    {
        return $query->orderByRaw("
            CASE priority 
                WHEN 'high' THEN 1 
                WHEN 'medium' THEN 2 
                WHEN 'low' THEN 3 
            END
        ")->orderBy('target_date');
    }
}