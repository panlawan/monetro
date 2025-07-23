<?php
// app/Models/Budget.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'month',
        'year',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
        'is_active' => 'boolean',
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

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getMonthNameAttribute(): string
    {
        return Carbon::create()->month($this->month)->format('F');
    }

    // Methods
    public function getSpentAmount(): float
    {
        return $this->category->transactions()
                    ->where('type', 'expense')
                    ->whereMonth('transaction_date', $this->month)
                    ->whereYear('transaction_date', $this->year)
                    ->sum('amount');
    }

    public function getRemainingAmount(): float
    {
        return $this->amount - $this->getSpentAmount();
    }

    public function getUsagePercentage(): float
    {
        if ($this->amount == 0) return 0;
        return ($this->getSpentAmount() / $this->amount) * 100;
    }

    public function isOverBudget(): bool
    {
        return $this->getSpentAmount() > $this->amount;
    }
}
