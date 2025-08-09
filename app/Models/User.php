<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'currency',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * User's accounts (กระเป๋าเงิน/บัญชี)
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * User's active accounts only
     */
    public function activeAccounts(): HasMany
    {
        return $this->hasMany(Account::class)->where('is_active', true);
    }

    /**
     * User's categories
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * User's active categories
     */
    public function activeCategories(): HasMany
    {
        return $this->hasMany(Category::class)->where('is_active', true);
    }

    /**
     * User's transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * User's transfers
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    /**
     * User's transaction tags
     */
    public function transactionTags(): HasMany
    {
        return $this->hasMany(TransactionTag::class);
    }

    /**
     * User's budget plans
     */
    public function budgetPlans(): HasMany
    {
        return $this->hasMany(BudgetPlan::class);
    }

    /**
     * User's financial goals
     */
    public function financialGoals(): HasMany
    {
        return $this->hasMany(FinancialGoal::class);
    }

    /**
     * User's recurring transactions
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * User's preferences
     */
    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * User's monthly summaries
     */
    public function monthlySummaries(): HasMany
    {
        return $this->hasMany(MonthlySummary::class);
    }

    /**
     * User roles (from existing auth system)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withTimestamps()
                    ->withPivot(['assigned_at', 'assigned_by', 'expires_at']);
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get user's avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        
        // Default gravatar
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=150";
    }

    /**
     * Get user's display timezone
     */
    public function getDisplayTimezoneAttribute(): string
    {
        return $this->timezone ?? 'Asia/Bangkok';
    }

    /**
     * Get user's display currency
     */
    public function getDisplayCurrencyAttribute(): string
    {
        return $this->currency ?? 'THB';
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Get total balance across all accounts
     */
    public function getTotalBalance(): float
    {
        return $this->activeAccounts()
                   ->where('is_include_in_total', true)
                   ->sum('current_balance');
    }

    /**
     * Get income/expense summary for date range
     */
    public function getIncomeExpenseSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $query = $this->transactions();
        
        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }
        
        $income = $query->clone()->where('type', 'income')->sum('amount');
        $expense = $query->clone()->where('type', 'expense')->sum('amount');
        
        return [
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
        ];
    }

    /**
     * Get default account for user
     */
    public function getDefaultAccount(): ?Account
    {
        if ($this->preferences && $this->preferences->default_account_id) {
            return $this->accounts()->find($this->preferences->default_account_id);
        }
        
        return $this->activeAccounts()->first();
    }

    /**
     * Check if user has any financial data
     */
    public function hasFinancialData(): bool
    {
        return $this->accounts()->exists() || 
               $this->transactions()->exists();
    }
}