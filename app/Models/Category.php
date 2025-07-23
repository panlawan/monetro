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

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'income' => 'รายรับ',
            'expense' => 'รายจ่าย',
            default => 'ไม่ระบุ'
        };
    }

    // Methods
    public function getTotalAmount($month = null, $year = null): float
    {
        $query = $this->transactions();
        
        if ($month && $year) {
            $query->whereMonth('transaction_date', $month)
                  ->whereYear('transaction_date', $year);
        }
        
        return $query->sum('amount');
    }

    public function getBudgetAmount($month, $year): float
    {
        $budget = $this->budgets()
                      ->where('month', $month)
                      ->where('year', $year)
                      ->first();
        
        return $budget ? $budget->amount : 0;
    }
}