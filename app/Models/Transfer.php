<?php
// app/Models/Transfer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'fee',
        'exchange_rate',
        'description',
        'transfer_date',
        'reference_number',
        'from_transaction_id',
        'to_transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'transfer_date' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Transfer belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transfer from account
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    /**
     * Transfer to account
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    /**
     * From transaction (expense transaction)
     */
    public function fromTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'from_transaction_id');
    }

    /**
     * To transaction (income transaction)
     */
    public function toTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'to_transaction_id');
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get formatted fee
     */
    public function getFormattedFeeAttribute(): string
    {
        return number_format($this->fee, 2);
    }

    /**
     * Get total deducted amount (amount + fee)
     */
    public function getTotalDeductedAttribute(): float
    {
        return $this->amount + $this->fee;
    }

    /**
     * Get amount received (after exchange rate)
     */
    public function getAmountReceivedAttribute(): float
    {
        return $this->amount * $this->exchange_rate;
    }

    /**
     * Get formatted transfer date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->transfer_date->format('d/m/Y H:i');
    }

    /**
     * Check if different currencies
     */
    public function getIsCurrencyExchangeAttribute(): bool
    {
        return $this->exchange_rate != 1.0000;
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Create transactions for this transfer
     */
    public function createTransactions(): void
    {
        // Create expense transaction for from_account
        $fromTransaction = Transaction::create([
            'user_id' => $this->user_id,
            'account_id' => $this->from_account_id,
            'category_id' => $this->getTransferCategoryId(),
            'type' => 'transfer',
            'amount' => $this->total_deducted,
            'description' => $this->description ?: "โอนเงินไป {$this->toAccount->name}",
            'transaction_date' => $this->transfer_date,
            'reference_number' => $this->reference_number,
        ]);

        // Create income transaction for to_account
        $toTransaction = Transaction::create([
            'user_id' => $this->user_id,
            'account_id' => $this->to_account_id,
            'category_id' => $this->getTransferCategoryId(),
            'type' => 'transfer',
            'amount' => $this->amount_received,
            'description' => $this->description ?: "รับเงินโอนจาก {$this->fromAccount->name}",
            'transaction_date' => $this->transfer_date,
            'reference_number' => $this->reference_number,
        ]);

        // Update transfer with transaction IDs
        $this->update([
            'from_transaction_id' => $fromTransaction->id,
            'to_transaction_id' => $toTransaction->id,
        ]);

        // Update account balances
        $this->updateAccountBalances();
    }

    /**
     * Update account balances
     */
    public function updateAccountBalances(): void
    {
        // Deduct from source account (amount + fee)
        $this->fromAccount->updateBalance($this->total_deducted, 'subtract');
        
        // Add to destination account (amount * exchange_rate)
        $this->toAccount->updateBalance($this->amount_received, 'add');
    }

    /**
     * Reverse account balance changes
     */
    public function reverseAccountBalances(): void
    {
        // Add back to source account
        $this->fromAccount->updateBalance($this->total_deducted, 'add');
        
        // Subtract from destination account
        $this->toAccount->updateBalance($this->amount_received, 'subtract');
    }

    /**
     * Get transfer category ID (create if not exists)
     */
    private function getTransferCategoryId(): int
    {
        $category = Category::firstOrCreate([
            'user_id' => $this->user_id,
            'name' => 'โอนเงิน',
            'type' => 'expense', // Default type for system category
        ], [
            'color' => '#6c757d',
            'icon' => 'fas fa-exchange-alt',
            'description' => 'หมวดหมู่สำหรับการโอนเงินระหว่างบัญชี',
            'is_active' => true,
            'sort_order' => 999,
        ]);

        return $category->id;
    }

    /**
     * Delete transfer and its transactions
     */
    public function deleteWithTransactions(): bool
    {
        // Reverse balance changes first
        $this->reverseAccountBalances();

        // Delete related transactions
        if ($this->fromTransaction) {
            $this->fromTransaction->delete();
        }
        if ($this->toTransaction) {
            $this->toTransaction->delete();
        }

        // Delete transfer
        return $this->delete();
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('transfer_date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific account (from or to)
     */
    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('from_account_id', $accountId)
                    ->orWhere('to_account_id', $accountId);
    }

    /**
     * Scope ordered by date (newest first)
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('transfer_date', 'desc');
    }

    /**
     * Scope with relationships loaded
     */
    public function scopeWithAccounts($query)
    {
        return $query->with(['fromAccount', 'toAccount']);
    }
}