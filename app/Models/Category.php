<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'color',
        'icon',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Category belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category's transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Category's recurring transactions
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * Budget categories using this category
     */
    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get category type label
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
     * Get category display name with icon
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get CSS class for type
     */
    public function getTypeCssClassAttribute(): string
    {
        return match($this->type) {
            'income' => 'text-green-600',
            'expense' => 'text-red-600',
            default => 'text-gray-600',
        };
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Calculate total amount for date range
     */
    public function getTotalAmount(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->transactions();
        
        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }
        
        return $query->sum('amount');
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(): array
    {
        return [
            'total_transactions' => $this->transactions()->count(),
            'total_amount' => $this->transactions()->sum('amount'),
            'average_amount' => $this->transactions()->avg('amount') ?? 0,
            'last_used_at' => $this->transactions()->max('transaction_date'),
        ];
    }

    /**
     * Check if category can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->transactions()->count() === 0 && 
               $this->recurringTransactions()->count() === 0;
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for income categories
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope for expense categories
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}