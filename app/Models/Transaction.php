<?php
// app/Models/Transaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'description',
        'notes',
        'transaction_date',
        'type',
        'is_recurring',
        'recurring_type',
        'recurring_interval',
        'recurring_end_date',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'recurring_end_date' => 'date',
        'is_recurring' => 'boolean',
        'recurring_interval' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('transaction_date', now()->year);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'income' => 'รายรับ',
            'expense' => 'รายจ่าย',
            default => 'ไม่ระบุ'
        };
    }

    public function getAmountWithSignAttribute(): string
    {
        $sign = $this->type === 'income' ? '+' : '-';
        return $sign . $this->formatted_amount;
    }

    // Methods
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }
}
