<?php
// app/Models/TransactionTag.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TransactionTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Tag belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tag's transactions (many-to-many)
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'transaction_tag_pivot', 'tag_id', 'transaction_id')
                    ->withTimestamps();
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Decrement usage count
     */
    public function decrementUsage(): void
    {
        if ($this->usage_count > 0) {
            $this->decrement('usage_count');
        }
    }

    /**
     * Update usage count based on actual transactions
     */
    public function updateUsageCount(): void
    {
        $this->usage_count = $this->transactions()->count();
        $this->save();
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for most used tags
     */
    public function scopeMostUsed($query, int $limit = 10)
    {
        return $query->orderBy('usage_count', 'desc')->limit($limit);
    }

    /**
     * Scope for tags with usage
     */
    public function scopeUsed($query)
    {
        return $query->where('usage_count', '>', 0);
    }
}