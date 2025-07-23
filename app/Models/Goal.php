<?php
// app/Models/Goal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return max($this->target_date->diffInDays(now()), 0);
    }

    public function getStatusDisplayNameAttribute(): string
    {
        return match($this->status) {
            'active' => 'กำลังดำเนินการ',
            'completed' => 'สำเร็จแล้ว',
            'paused' => 'หยุดชั่วคราว',
            'cancelled' => 'ยกเลิกแล้ว',
            default => 'ไม่ระบุ'
        };
    }

    // Methods
    public function isCompleted(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }

    public function addProgress(float $amount): void
    {
        $this->current_amount += $amount;
        
        if ($this->isCompleted()) {
            $this->status = 'completed';
        }
        
        $this->save();
    }
}