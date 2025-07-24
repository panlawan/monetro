<?php 
// app/Models/Category.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'icon', 'color', 'type', 'description', 'is_active', 'user_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Methods
    public function getTotalTransactions($startDate = null, $endDate = null)
    {
        $query = $this->transactions();
        
        if ($startDate) $query->where('transaction_date', '>=', $startDate);
        if ($endDate) $query->where('transaction_date', '<=', $endDate);
        
        return $query->sum('amount');
    }

    public static function getDefaultCategories($type = 'expense')
    {
        $categories = [
            'expense' => [
                ['name' => 'Food & Dining', 'icon' => 'utensils', 'color' => '#ff6b6b'],
                ['name' => 'Transportation', 'icon' => 'car', 'color' => '#4ecdc4'],
                ['name' => 'Shopping', 'icon' => 'shopping-bag', 'color' => '#45b7d1'],
                ['name' => 'Entertainment', 'icon' => 'film', 'color' => '#96ceb4'],
                ['name' => 'Bills & Utilities', 'icon' => 'file-invoice', 'color' => '#feca57'],
                ['name' => 'Healthcare', 'icon' => 'heartbeat', 'color' => '#ff9ff3'],
                ['name' => 'Education', 'icon' => 'graduation-cap', 'color' => '#54a0ff'],
            ],
            'income' => [
                ['name' => 'Salary', 'icon' => 'money-bill-wave', 'color' => '#2ecc71'],
                ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#3498db'],
                ['name' => 'Investment', 'icon' => 'chart-line', 'color' => '#9b59b6'],
                ['name' => 'Gift', 'icon' => 'gift', 'color' => '#e74c3c'],
                ['name' => 'Other Income', 'icon' => 'plus-circle', 'color' => '#f39c12'],
            ]
        ];

        return $categories[$type] ?? [];
    }
}