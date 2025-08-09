<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'account_number',
        'bank_name',
        'initial_balance',
        'current_balance',
        'credit_limit',
        'color',
        'icon',
        'is_active',
        'is_include_in_total',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'is_include_in_total' => 'boolean',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Account belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Account's transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Transfers from this account
     */
    public function transfersFrom(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_account_id');
    }

    /**
     * Transfers to this account
     */
    public function transfersTo(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_account_id');
    }

    /**
     * All transfers (from + to)
     */
    public function allTransfers()
    {
        return Transfer::where('from_account_id', $this->id)
                      ->orWhere('to_account_id', $this->id);
    }

    /**
     * Recurring transactions using this account
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * Financial goals linked to this account
     */
    public function linkedGoals(): HasMany
    {
        return $this->hasMany(FinancialGoal::class, 'linked_account_id');
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get account type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'cash' => 'เงินสด',
            'bank' => 'บัญชีธนาคาร',
            'credit_card' => 'บัตรเครดิต',
            'e_wallet' => 'กระเป๋าเงินดิจิทัล',
            default => $this->type,
        };
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->current_balance, 2);
    }

    /**
     * Get available credit (for credit cards)
     */
    public function getAvailableCreditAttribute(): float
    {
        if ($this->type === 'credit_card' && $this->credit_limit) {
            return $this->credit_limit + $this->current_balance; // balance is negative for debt
        }
        
        return 0;
    }

    /**
     * Check if account is credit card
     */
    public function getIsCreditCardAttribute(): bool
    {
        return $this->type === 'credit_card';
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Update account balance
     */
    public function updateBalance(float $amount, string $operation = 'add'): void
    {
        if ($operation === 'add') {
            $this->current_balance += $amount;
        } else {
            $this->current_balance -= $amount;
        }
        
        $this->save();
    }

    /**
     * Calculate total income for date range
     */
    public function getTotalIncome(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->transactions()->where('type', 'income');
        
        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }
        
        return $query->sum('amount');
    }

    /**
     * Calculate total expense for date range
     */
    public function getTotalExpense(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->transactions()->where('type', 'expense');
        
        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }
        
        return $query->sum('amount');
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions(int $limit = 10)
    {
        return $this->transactions()
                   ->with(['category'])
                   ->orderBy('transaction_date', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Check if account can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->transactions()->count() === 0 && 
               $this->transfersFrom()->count() === 0 && 
               $this->transfersTo()->count() === 0;
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for accounts included in total
     */
    public function scopeIncludedInTotal($query)
    {
        return $query->where('is_include_in_total', true);
    }

    /**
     * Scope by account type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}