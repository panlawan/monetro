<?php
// app/Models/Transaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'amount', 'description', 'transaction_date',
        'category_id', 'user_id', 'payment_method', 'reference_number',
        'notes', 'location', 'tags', 'unit_price', 'quantity', 'symbol',
        'status', 'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'quantity' => 'decimal:4',
        'transaction_date' => 'date',
        'processed_at' => 'datetime',
        'tags' => 'array',
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

    // Scopes
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeInvestment($query)
    {
        return $query->where('type', 'investment');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('transaction_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    public function scopeThisYear($query)
    {
        return $query->whereBetween('transaction_date', [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        ]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Accessors & Mutators
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getAbsoluteAmountAttribute()
    {
        return abs($this->amount);
    }

    public function getDisplayAmountAttribute()
    {
        $prefix = $this->type === 'income' ? '+' : '-';
        return $prefix . '฿' . number_format(abs($this->amount), 2);
    }

    // Methods
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    public function isInvestment(): bool
    {
        return $this->type === 'investment';
    }

    public static function getMonthlyTrend($userId, $months = 6)
    {
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();
        
        return self::forUser($userId)
            ->where('transaction_date', '>=', $startDate)
            ->selectRaw("
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN ABS(amount) ELSE 0 END) as expense
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public static function getExpenseByCategory($userId, $startDate = null, $endDate = null)
    {
        $query = self::forUser($userId)
            ->expense()
            ->with('category')
            ->selectRaw('category_id, SUM(ABS(amount)) as total')
            ->groupBy('category_id')
            ->orderBy('total', 'desc');

        if ($startDate) $query->where('transaction_date', '>=', $startDate);
        if ($endDate) $query->where('transaction_date', '<=', $endDate);

        return $query->get();
    }
}