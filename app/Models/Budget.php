<?php
// app/Models/Budget.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'amount', 'spent', 'period', 'start_date', 'end_date',
        'category_id', 'user_id', 'alert_enabled', 'alert_percentage', 'is_active'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'alert_enabled' => 'boolean',
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
    public function getPercentageAttribute()
    {
        return $this->amount > 0 ? ($this->spent / $this->amount) * 100 : 0;
    }

    public function getRemainingAttribute()
    {
        return $this->amount - $this->spent;
    }

    public function getDaysRemainingAttribute()
    {
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    // Methods
    public function updateSpent()
    {
        $this->spent = Transaction::forUser($this->user_id)
            ->where('category_id', $this->category_id)
            ->expense()
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->sum('amount');
        
        $this->save();
        
        return $this;
    }

    public function isOverBudget(): bool
    {
        return $this->spent > $this->amount;
    }

    public function shouldAlert(): bool
    {
        return $this->alert_enabled && $this->percentage >= $this->alert_percentage;
    }

    public function getStatusColor(): string
    {
        $percentage = $this->percentage;
        
        if ($percentage >= 100) return 'danger';
        if ($percentage >= $this->alert_percentage) return 'warning';
        return 'success';
    }
}