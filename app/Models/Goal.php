<?php
// app/Models/Goal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'target_date',
        'status',
        'color',
        'icon',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Accessors
    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount == 0) return 0;
        return min(($this->current_amount / $this->target_amount) * 100, 100);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max($this->target_amount - $this->current_amount, 0);
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->target_date->isPast()) return 0;
        return now()->diffInDays($this->target_date);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->target_date->isPast() && $this->status !== 'completed';
    }

    public function getFormattedTargetAmountAttribute(): string
    {
        return number_format($this->target_amount, 2);
    }

    public function getFormattedCurrentAmountAttribute(): string
    {
        return number_format($this->current_amount, 2);
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return number_format($this->remaining_amount, 2);
    }

    // Methods
    public function addProgress(float $amount): bool
    {
        $this->current_amount = min($this->current_amount + $amount, $this->target_amount);
        
        if ($this->current_amount >= $this->target_amount) {
            $this->status = 'completed';
        }
        
        return $this->save();
    }

    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        $this->current_amount = $this->target_amount;
        return $this->save();
    }
}