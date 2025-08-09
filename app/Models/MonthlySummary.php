<?php
// app/Models/MonthlySummary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'total_income',
        'total_expense',
        'net_income',
        'total_transfers',
        'transaction_count',
        'transfer_count',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_income' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_income' => 'decimal:2',
        'total_transfers' => 'decimal:2',
        'transaction_count' => 'integer',
        'transfer_count' => 'integer',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Summary belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get month name in Thai
     */
    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
            4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
            10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];

        return $months[$this->month] ?? 'ไม่ระบุ';
    }

    /**
     * Get period label
     */
    public function getPeriodLabelAttribute(): string
    {
        return "{$this->month_name} {$this->year}";
    }

    /**
     * Get formatted amounts
     */
    public function getFormattedTotalIncomeAttribute(): string
    {
        return number_format($this->total_income, 2);
    }

    public function getFormattedTotalExpenseAttribute(): string
    {
        return number_format($this->total_expense, 2);
    }

    public function getFormattedNetIncomeAttribute(): string
    {
        return number_format($this->net_income, 2);
    }

    /**
     * Get savings rate percentage
     */
    public function getSavingsRateAttribute(): float
    {
        if ($this->total_income <= 0) return 0;
        return ($this->net_income / $this->total_income) * 100;
    }

    /**
     * Get expense ratio percentage
     */
    public function getExpenseRatioAttribute(): float
    {
        if ($this->total_income <= 0) return 0;
        return ($this->total_expense / $this->total_income) * 100;
    }

    /**
     * Check if profitable month
     */
    public function getIsProfitableAttribute(): bool
    {
        return $this->net_income > 0;
    }

    /**
     * Get status with color based on net income
     */
    public function getStatusAttribute(): array
    {
        if ($this->net_income > 0) {
            return [
                'status' => 'profitable',
                'label' => 'กำไร',
                'color' => 'green',
                'css_class' => 'text-green-600 bg-green-100',
            ];
        } elseif ($this->net_income < 0) {
            return [
                'status' => 'loss',
                'label' => 'ขาดทุน',
                'color' => 'red',
                'css_class' => 'text-red-600 bg-red-100',
            ];
        } else {
            return [
                'status' => 'break_even',
                'label' => 'เท่าทุน',
                'color' => 'yellow',
                'css_class' => 'text-yellow-600 bg-yellow-100',
            ];
        }
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Recalculate summary from actual transactions
     */
    public function recalculate(): void
    {
        $startDate = "{$this->year}-{$this->month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Calculate income
        $totalIncome = Transaction::where('user_id', $this->user_id)
                                 ->where('type', 'income')
                                 ->whereBetween('transaction_date', [$startDate, $endDate])
                                 ->sum('amount');

        // Calculate expense
        $totalExpense = Transaction::where('user_id', $this->user_id)
                                  ->where('type', 'expense')
                                  ->whereBetween('transaction_date', [$startDate, $endDate])
                                  ->sum('amount');

        // Calculate transfers
        $totalTransfers = Transfer::where('user_id', $this->user_id)
                                 ->whereBetween('transfer_date', [$startDate, $endDate])
                                 ->sum('amount');

        // Count transactions
        $transactionCount = Transaction::where('user_id', $this->user_id)
                                      ->whereBetween('transaction_date', [$startDate, $endDate])
                                      ->count();

        // Count transfers
        $transferCount = Transfer::where('user_id', $this->user_id)
                                ->whereBetween('transfer_date', [$startDate, $endDate])
                                ->count();

        // Update summary
        $this->update([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_income' => $totalIncome - $totalExpense,
            'total_transfers' => $totalTransfers,
            'transaction_count' => $transactionCount,
            'transfer_count' => $transferCount,
        ]);
    }

    /**
     * Compare with previous month
     */
    public function compareWithPreviousMonth(): ?array
    {
        $prevMonth = $this->month - 1;
        $prevYear = $this->year;

        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $previousSummary = static::where('user_id', $this->user_id)
                                ->where('year', $prevYear)
                                ->where('month', $prevMonth)
                                ->first();

        if (!$previousSummary) {
            return null;
        }

        return [
            'income_change' => $this->total_income - $previousSummary->total_income,
            'income_change_percent' => $previousSummary->total_income > 0 
                ? (($this->total_income - $previousSummary->total_income) / $previousSummary->total_income) * 100 
                : 0,
            'expense_change' => $this->total_expense - $previousSummary->total_expense,
            'expense_change_percent' => $previousSummary->total_expense > 0 
                ? (($this->total_expense - $previousSummary->total_expense) / $previousSummary->total_expense) * 100 
                : 0,
            'net_change' => $this->net_income - $previousSummary->net_income,
            'savings_rate_change' => $this->savings_rate - $previousSummary->savings_rate,
        ];
    }

    /**
     * Get category breakdown for this month
     */
    public function getCategoryBreakdown(): array
    {
        $startDate = "{$this->year}-{$this->month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        return Transaction::with('category')
                         ->where('user_id', $this->user_id)
                         ->whereBetween('transaction_date', [$startDate, $endDate])
                         ->selectRaw('category_id, type, SUM(amount) as total, COUNT(*) as count')
                         ->groupBy('category_id', 'type')
                         ->get()
                         ->groupBy('type')
                         ->toArray();
    }

    /**
     * Get daily averages
     */
    public function getDailyAverages(): array
    {
        $daysInMonth = date('t', strtotime("{$this->year}-{$this->month}-01"));

        return [
            'daily_income' => $this->total_income / $daysInMonth,
            'daily_expense' => $this->total_expense / $daysInMonth,
            'daily_net' => $this->net_income / $daysInMonth,
            'daily_transactions' => $this->transaction_count / $daysInMonth,
        ];
    }

    // ================================
    // STATIC METHODS
    // ================================

    /**
     * Generate or update summary for specific month
     */
    public static function generateForMonth(int $userId, int $year, int $month): self
    {
        $summary = static::firstOrNew([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month,
        ]);

        $summary->recalculate();
        return $summary;
    }

    /**
     * Generate summaries for date range
     */
    public static function generateForDateRange(int $userId, string $startDate, string $endDate): void
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        while ($start->lte($end)) {
            static::generateForMonth($userId, $start->year, $start->month);
            $start->addMonth();
        }
    }

    /**
     * Get yearly summary
     */
    public static function getYearlySummary(int $userId, int $year): array
    {
        $summaries = static::where('user_id', $userId)
                           ->where('year', $year)
                           ->orderBy('month')
                           ->get();

        return [
            'total_income' => $summaries->sum('total_income'),
            'total_expense' => $summaries->sum('total_expense'),
            'net_income' => $summaries->sum('net_income'),
            'total_transfers' => $summaries->sum('total_transfers'),
            'transaction_count' => $summaries->sum('transaction_count'),
            'transfer_count' => $summaries->sum('transfer_count'),
            'monthly_data' => $summaries->toArray(),
            'best_month' => $summaries->where('net_income', $summaries->max('net_income'))->first(),
            'worst_month' => $summaries->where('net_income', $summaries->min('net_income'))->first(),
            'average_monthly_income' => $summaries->avg('total_income'),
            'average_monthly_expense' => $summaries->avg('total_expense'),
        ];
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for specific year
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope for date range
     */
    public function scopeForDateRange($query, string $startDate, string $endDate)
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        return $query->where(function($q) use ($start, $end) {
            $q->where(function($subQ) use ($start, $end) {
                $subQ->where('year', '>', $start->year)
                     ->where('year', '<', $end->year);
            })->orWhere(function($subQ) use ($start, $end) {
                $subQ->where('year', $start->year)
                     ->where('month', '>=', $start->month);
            })->orWhere(function($subQ) use ($start, $end) {
                $subQ->where('year', $end->year)
                     ->where('month', '<=', $end->month);
            });
        });
    }

    /**
     * Scope for profitable months
     */
    public function scopeProfitable($query)
    {
        return $query->where('net_income', '>', 0);
    }

    /**
     * Scope for loss months
     */
    public function scopeLoss($query)
    {
        return $query->where('net_income', '<', 0);
    }

    /**
     * Scope ordered by period (newest first)
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('year', 'desc')->orderBy('month', 'desc');
    }

    /**
     * Scope ordered by period (oldest first)
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('year', 'asc')->orderBy('month', 'asc');
    }
}