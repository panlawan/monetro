<?php
// app/Models/UserPreference.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'default_account_id',
        'default_currency',
        'date_format',
        'number_format',
        'start_of_week',
        'fiscal_year_start',
        'dashboard_widgets',
        'notification_settings',
        'theme_preference',
    ];

    protected $casts = [
        'fiscal_year_start' => 'integer',
        'dashboard_widgets' => 'array',
        'notification_settings' => 'array',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Preference belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Default account
     */
    public function defaultAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'default_account_id');
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get date format label
     */
    public function getDateFormatLabelAttribute(): string
    {
        return match($this->date_format) {
            'd/m/Y' => 'วัน/เดือน/ปี (31/12/2024)',
            'm/d/Y' => 'เดือน/วัน/ปี (12/31/2024)',
            'Y-m-d' => 'ปี-เดือน-วัน (2024-12-31)',
            default => $this->date_format,
        };
    }

    /**
     * Get number format label
     */
    public function getNumberFormatLabelAttribute(): string
    {
        return match($this->number_format) {
            'comma_dot' => '1,234.56',
            'dot_comma' => '1.234,56',
            'space_dot' => '1 234.56',
            'space_comma' => '1 234,56',
            default => $this->number_format,
        };
    }

    /**
     * Get start of week label
     */
    public function getStartOfWeekLabelAttribute(): string
    {
        return match($this->start_of_week) {
            'sunday' => 'วันอาทิตย์',
            'monday' => 'วันจันทร์',
            default => $this->start_of_week,
        };
    }

    /**
     * Get theme preference label
     */
    public function getThemePreferenceLabelAttribute(): string
    {
        return match($this->theme_preference) {
            'light' => 'สว่าง',
            'dark' => 'มืด',
            'auto' => 'อัตโนมัติ',
            default => $this->theme_preference,
        };
    }

    /**
     * Get fiscal year start month name
     */
    public function getFiscalYearStartMonthAttribute(): string
    {
        $months = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
            4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
            10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];

        return $months[$this->fiscal_year_start] ?? 'มกราคม';
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Format number according to user preference
     */
    public function formatNumber(float $number, int $decimals = 2): string
    {
        return match($this->number_format) {
            'comma_dot' => number_format($number, $decimals, '.', ','),
            'dot_comma' => number_format($number, $decimals, ',', '.'),
            'space_dot' => number_format($number, $decimals, '.', ' '),
            'space_comma' => number_format($number, $decimals, ',', ' '),
            default => number_format($number, $decimals, '.', ','),
        };
    }

    /**
     * Format date according to user preference
     */
    public function formatDate(\DateTime $date): string
    {
        return $date->format($this->date_format);
    }

    /**
     * Get dashboard widget configuration
     */
    public function getDashboardWidgets(): array
    {
        return $this->dashboard_widgets ?? [
            'account_summary' => ['enabled' => true, 'order' => 1],
            'recent_transactions' => ['enabled' => true, 'order' => 2],
            'monthly_summary' => ['enabled' => true, 'order' => 3],
            'budget_overview' => ['enabled' => true, 'order' => 4],
            'financial_goals' => ['enabled' => true, 'order' => 5],
        ];
    }

    /**
     * Get notification settings
     */
    public function getNotificationSettings(): array
    {
        return $this->notification_settings ?? [
            'budget_exceeded' => true,
            'goal_achieved' => true,
            'recurring_due' => true,
            'low_balance' => true,
            'monthly_report' => false,
            'email_notifications' => true,
            'push_notifications' => false,
        ];
    }

    /**
     * Update dashboard widget order
     */
    public function updateDashboardWidgets(array $widgets): void
    {
        $this->update(['dashboard_widgets' => $widgets]);
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(array $settings): void
    {
        $currentSettings = $this->getNotificationSettings();
        $newSettings = array_merge($currentSettings, $settings);
        $this->update(['notification_settings' => $newSettings]);
    }

    /**
     * Get fiscal year start date for given year
     */
    public function getFiscalYearStartDate(int $year): \Carbon\Carbon
    {
        return \Carbon\Carbon::createFromDate($year, $this->fiscal_year_start, 1);
    }

    /**
     * Get fiscal year end date for given year
     */
    public function getFiscalYearEndDate(int $year): \Carbon\Carbon
    {
        $startDate = $this->getFiscalYearStartDate($year);
        return $startDate->copy()->addYear()->subDay();
    }

    /**
     * Get current fiscal year
     */
    public function getCurrentFiscalYear(): array
    {
        $now = now();
        $fiscalYear = $now->year;
        
        // If current month is before fiscal year start, use previous year
        if ($now->month < $this->fiscal_year_start) {
            $fiscalYear--;
        }

        return [
            'year' => $fiscalYear,
            'start_date' => $this->getFiscalYearStartDate($fiscalYear),
            'end_date' => $this->getFiscalYearEndDate($fiscalYear),
        ];
    }

    // ================================
    // STATIC METHODS
    // ================================

    /**
     * Get default preferences
     */
    public static function getDefaults(): array
    {
        return [
            'default_currency' => 'THB',
            'date_format' => 'd/m/Y',
            'number_format' => 'comma_dot',
            'start_of_week' => 'sunday',
            'fiscal_year_start' => 1,
            'theme_preference' => 'light',
            'dashboard_widgets' => [
                'account_summary' => ['enabled' => true, 'order' => 1],
                'recent_transactions' => ['enabled' => true, 'order' => 2],
                'monthly_summary' => ['enabled' => true, 'order' => 3],
                'budget_overview' => ['enabled' => true, 'order' => 4],
                'financial_goals' => ['enabled' => true, 'order' => 5],
            ],
            'notification_settings' => [
                'budget_exceeded' => true,
                'goal_achieved' => true,
                'recurring_due' => true,
                'low_balance' => true,
                'monthly_report' => false,
                'email_notifications' => true,
                'push_notifications' => false,
            ],
        ];
    }
}