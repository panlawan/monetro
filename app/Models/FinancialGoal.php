<?php
// app/Models/FinancialGoal.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FinancialGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'target_amount', 'current_amount',
        'target_date', 'type', 'monthly_contribution', 'auto_calculate',
        'user_id', 'status'
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'monthly_contribution' => 'decimal:2',
        'target_date' => 'date',
        'auto_calculate' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getProgressPercentageAttribute()
    {
        return $this->target_amount > 0 ? ($this->current_amount / $this->target_amount) * 100 : 0;
    }

    public function getRemainingAmountAttribute()
    {
        return $this->target_amount - $this->current_amount;
    }

    public function getMonthsRemainingAttribute()
    {
        return Carbon::now()->diffInMonths($this->target_date, false);
    }

    public function getRequiredMonthlyContributionAttribute()
    {
        $monthsRemaining = $this->months_remaining;
        return $monthsRemaining > 0 ? $this->remaining_amount / $monthsRemaining : 0;
    }

    // Methods
    public function updateProgress()
    {
        if ($this->auto_calculate) {
            // คำนวณจากการทำธุรกรรมจริง (ตาม type)
            $this->calculateFromTransactions();
        }
        
        // Check if goal is completed
        if ($this->current_amount >= $this->target_amount) {
            $this->status = 'completed';
        }
        
        $this->save();
        return $this;
    }

    private function calculateFromTransactions()
    {
        // Logic for calculating progress based on actual transactions
        // This would depend on the goal type
        switch ($this->type) {
            case 'savings':
                // Sum of savings transactions
                break;
            case 'investment':
                // Sum of investment transactions
                break;
            case 'debt_payoff':
                // Sum of debt payment transactions
                break;
        }
    }

    public function isOnTrack(): bool
    {
        $expectedProgress = $this->getExpectedProgress();
        return $this->progress_percentage >= $expectedProgress;
    }

    public function getExpectedProgress(): float
    {
        $totalDays = Carbon::parse($this->created_at)->diffInDays($this->target_date);
        $daysPassed = Carbon::parse($this->created_at)->diffInDays(Carbon::now());
        
        return $totalDays > 0 ? ($daysPassed / $totalDays) * 100 : 0;
    }
}