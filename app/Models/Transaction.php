<?php
// app/Models/Transaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'reference_number',
        'location',
        'notes',
        'is_recurring',
        'recurring_type',
        'recurring_end_date',
        'parent_transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'recurring_end_date' => 'date',
        'is_recurring' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Transaction belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transaction belongs to account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Transaction belongs to category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Transaction tags (many-to-many)
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TransactionTag::class, 'transaction_tag_pivot', 'transaction_id', 'tag_id')
                    ->withTimestamps();
    }

    /**
     * Transaction attachments
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TransactionAttachment::class);
    }

    /**
     * Parent transaction (for recurring transactions)
     */
    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Child transactions (generated from recurring)
     */
    public function childTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Related transfer (if this transaction is part of a transfer)
     */
    public function fromTransfer(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_transaction_id');
    }

    /**
     * Related transfer (if this transaction is part of a transfer)
     */
    public function toTransfer(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_transaction_id');
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get transaction type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'income' => 'รายรับ',
            'expense' => 'รายจ่าย',
            'transfer' => 'โอนเงิน',
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
     * Get amount with sign
     */
    public function getSignedAmountAttribute(): float
    {
        return match($this->type) {
            'income' => $this->amount,
            'expense', 'transfer' => -$this->amount,
            default => $this->amount,
        };
    }

    /**
     * Get CSS class for amount color
     */
    public function getAmountCssClassAttribute(): string
    {
        return match($this->type) {
            'income' => 'text-green-600',
            'expense' => 'text-red-600',
            'transfer' => 'text-blue-600',
            default => 'text-gray-600',
        };
    }

    /**
     * Get formatted transaction date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->transaction_date->format('d/m/Y H:i');
    }

    /**
     * Check if transaction is from transfer
     */
    public function getIsTransferAttribute(): bool
    {
        return $this->type === 'transfer' || 
               $this->fromTransfer()->exists() || 
               $this->toTransfer()->exists();
    }

    /**
     * Check if transaction is recurring
     */
    public function getIsRecurringTransactionAttribute(): bool
    {
        return $this->is_recurring || $this->parent_transaction_id !== null;
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Get transaction tags as comma-separated string
     */
    public function getTagsString(): string
    {
        return $this->tags->pluck('name')->join(', ');
    }

    /**
     * Check if transaction can be edited
     */
    public function canBeEdited(): bool
    {
        // Recurring child transactions ไม่ควร edit
        return $this->parent_transaction_id === null;
    }

    /**
     * Check if transaction can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Transfer transactions ต้องลบผ่าน Transfer model
        return !$this->is_transfer;
    }

    /**
     * Update account balance after transaction
     */
    public function updateAccountBalance(): void
    {
        if ($this->type === 'income') {
            $this->account->updateBalance($this->amount, 'add');
        } elseif ($this->type === 'expense') {
            $this->account->updateBalance($this->amount, 'subtract');
        }
        // Transfer transactions จะถูก handle ใน Transfer model
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope by transaction type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for income transactions
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope for expense transactions
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope for transfer transactions
     */
    public function scopeTransfer($query)
    {
        return $query->where('type', 'transfer');
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for current month
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereBetween('transaction_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    /**
     * Scope for recurring transactions only
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope for non-recurring transactions
     */
    public function scopeNonRecurring($query)
    {
        return $query->where('is_recurring', false)
                    ->whereNull('parent_transaction_id');
    }

    /**
     * Scope ordered by date (newest first)
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('transaction_date', 'desc');
    }

    /**
     * Scope with common relationships loaded
     */
    public function scopeWithDetails($query)
    {
        return $query->with(['account', 'category', 'tags']);
    }
}