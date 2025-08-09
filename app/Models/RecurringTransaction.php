<?php
// app/Models/RecurringTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_name',
        'account_id',
        'category_id',
        'type',
        'amount',
        'description',
        'frequency',
        'interval_value',
        'start_date',
        'end_date',
        'next_due_date',
        'last_generated_date',
        'is_active',
        'auto_generate',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interval_value' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_due_date' => 'date',
        'last_generated_date' => 'date',
        'is_active' => 'boolean',
        'auto_generate' => 'boolean',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Recurring transaction belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Recurring transaction belongs to account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Recurring transaction belongs to category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get frequency label
     */
    public function getFrequencyLabelAttribute(): string
    {
        $label = match($this->frequency) {
            'daily' => 'รายวัน',
            'weekly' => 'รายสัปดาห์',
            'monthly' => 'รายเดือน',
            'quarterly' => 'รายไตรมาส',
            'yearly' => 'รายปี',
            default => $this->frequency,
        };

        if ($this->interval_value > 1) {
            $label .= " (ทุก {$this->interval_value} ครั้ง)";
        }

        return $label;
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'income' => 'รายรับ',
            'expense' => 'รายจ่าย',
            default => $this->type,
        };
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Check if overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->next_due_date && 
               now()->toDateString() > $this->next_due_date->toDateString();
    }

    /**
     * Check if due today
     */
    public function getIsDueTodayAttribute(): bool
    {
        return $this->next_due_date && 
               now()->toDateString() === $this->next_due_date->toDateString();
    }

    /**
     * Check if due soon (within 3 days)
     */
    public function getIsDueSoonAttribute(): bool
    {
        return $this->next_due_date && 
               $this->next_due_date->diffInDays(now(), false) <= 3 &&
               $this->next_due_date->diffInDays(now(), false) >= 0;
    }

    /**
     * Check if expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && 
               now()->toDateString() > $this->end_date->toDateString();
    }

    /**
     * Get status with color
     */
    public function getStatusAttribute(): array
    {
        if (!$this->is_active) {
            return [
                'status' => 'inactive',
                'label' => 'ไม่ใช้งาน',
                'color' => 'gray',
                'css_class' => 'text-gray-600 bg-gray-100',
            ];
        } elseif ($this->is_expired) {
            return [
                'status' => 'expired',
                'label' => 'หมดอายุ',
                'color' => 'red',
                'css_class' => 'text-red-600 bg-red-100',
            ];
        } elseif ($this->is_overdue) {
            return [
                'status' => 'overdue',
                'label' => 'เลยกำหนด',
                'color' => 'red',
                'css_class' => 'text-red-600 bg-red-100',
            ];
        } elseif ($this->is_due_today) {
            return [
                'status' => 'due_today',
                'label' => 'ถึงกำหนดวันนี้',
                'color' => 'orange',
                'css_class' => 'text-orange-600 bg-orange-100',
            ];
        } elseif ($this->is_due_soon) {
            return [
                'status' => 'due_soon',
                'label' => 'ใกล้ถึงกำหนด',
                'color' => 'yellow',
                'css_class' => 'text-yellow-600 bg-yellow-100',
            ];
        } else {
            return [
                'status' => 'active',
                'label' => 'ใช้งานอยู่',
                'color' => 'green',
                'css_class' => 'text-green-600 bg-green-100',
            ];
        }
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Calculate next due date
     */
    public function calculateNextDueDate(?Carbon $fromDate = null): Carbon
    {
        $fromDate = $fromDate ?? $this->next_due_date ?? $this->start_date;
        
        return match($this->frequency) {
            'daily' => $fromDate->addDays($this->interval_value),
            'weekly' => $fromDate->addWeeks($this->interval_value),
            'monthly' => $fromDate->addMonths($this->interval_value),
            'quarterly' => $fromDate->addMonths($this->interval_value * 3),
            'yearly' => $fromDate->addYears($this->interval_value),
            default => $fromDate->addMonths($this->interval_value),
        };
    }

    /**
     * Generate transaction for current due date
     */
    public function generateTransaction(): ?Transaction
    {
        if (!$this->isDue()) {
            return null;
        }

        $transaction = Transaction::create([
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'transaction_date' => $this->next_due_date,
            'is_recurring' => true,
            'parent_transaction_id' => null, // This is generated from recurring template
        ]);

        // Update recurring transaction
        $this->update([
            'last_generated_date' => $this->next_due_date,
            'next_due_date' => $this->calculateNextDueDate(),
        ]);

        // Update account balance
        $transaction->updateAccountBalance();

        return $transaction;
    }

    /**
     * Check if transaction is due for generation
     */
    public function isDue(): bool
    {
        return $this->is_active &&
               !$this->is_expired &&
               $this->next_due_date &&
               now()->toDateString() >= $this->next_due_date->toDateString();
    }

    /**
     * Get all due dates for a date range
     */
    public function getDueDatesInRange(Carbon $startDate, Carbon $endDate): array
    {
        $dates = [];
        $currentDate = $this->next_due_date->copy();

        while ($currentDate->lte($endDate) && ($this->end_date === null || $currentDate->lte($this->end_date))) {
            if ($currentDate->gte($startDate)) {
                $dates[] = $currentDate->copy();
            }
            $currentDate = $this->calculateNextDueDate($currentDate);
        }

        return $dates;
    }

    /**
     * Calculate total amount for a period
     */
    public function getTotalAmountForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        $dueDates = $this->getDueDatesInRange($startDate, $endDate);
        return count($dueDates) * $this->amount;
    }

    /**
     * Preview next few occurrences
     */
    public function getNextOccurrences(int $count = 5): array
    {
        $occurrences = [];
        $currentDate = $this->next_due_date->copy();

        for ($i = 0; $i < $count; $i++) {
            if ($this->end_date && $currentDate->gt($this->end_date)) {
                break;
            }

            $occurrences[] = [
                'date' => $currentDate->copy(),
                'formatted_date' => $currentDate->format('d/m/Y'),
                'amount' => $this->amount,
                'formatted_amount' => $this->formatted_amount,
            ];

            $currentDate = $this->calculateNextDueDate($currentDate);
        }

        return $occurrences;
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for active recurring transactions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for due transactions
     */
    public function scopeDue($query)
    {
        return $query->where('is_active', true)
                    ->where('next_due_date', '<=', now()->toDateString())
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now()->toDateString());
                    });
    }

    /**
     * Scope for auto-generate transactions
     */
    public function scopeAutoGenerate($query)
    {
        return $query->where('auto_generate', true);
    }

    /**
     * Scope by transaction type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by frequency
     */
    public function scopeOfFrequency($query, string $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope ordered by next due date
     */
    public function scopeByDueDate($query)
    {
        return $query->orderBy('next_due_date');
    }
}