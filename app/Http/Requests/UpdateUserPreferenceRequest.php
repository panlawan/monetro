<?php
// app/Http/Requests/UpdateUserPreferenceRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserPreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->is_active && 
               $this->user()->can('update', $this->route('user_preference'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'default_account_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $this->user()->id)
            ],
            'default_currency' => [
                'nullable',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/'
            ],
            'date_format' => [
                'nullable',
                'string',
                Rule::in(['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'm-d-Y'])
            ],
            'number_format' => [
                'nullable',
                'string',
                Rule::in(['comma_dot', 'dot_comma', 'space_dot', 'space_comma'])
            ],
            'start_of_week' => [
                'nullable',
                'string',
                Rule::in(['sunday', 'monday'])
            ],
            'fiscal_year_start' => [
                'nullable',
                'integer',
                'min:1',
                'max:12'
            ],
            'dashboard_widgets' => [
                'nullable',
                'array'
            ],
            'dashboard_widgets.*.name' => [
                'required',
                'string',
                'max:50'
            ],
            'dashboard_widgets.*.enabled' => [
                'nullable',
                'boolean'
            ],
            'dashboard_widgets.*.order' => [
                'nullable',
                'integer',
                'min:1',
                'max:20'
            ],
            'notification_settings' => [
                'nullable',
                'array'
            ],
            'notification_settings.budget_exceeded' => [
                'nullable',
                'boolean'
            ],
            'notification_settings.goal_achieved' => [
                'nullable',
                'boolean'
            ],
            'notification_settings.recurring_due' => [
                'nullable',
                'boolean'
            ],
            'notification_settings.low_balance' => [
                'nullable',
                'boolean'
            ],
            'notification_settings.monthly_report' => [
                'nullable',
                'boolean'
            ],
            'notification_settings.email_notifications' => [
                'nullable',
                'boolean'
            ],
            'notification_settings.push_notifications' => [
                'nullable',
                'boolean'
            ],
            'theme_preference' => [
                'nullable',
                'string',
                Rule::in(['light', 'dark', 'auto'])
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'default_account_id.exists' => 'บัญชีเริ่มต้นที่เลือกไม่ถูกต้อง',
            
            'default_currency.size' => 'สกุลเงินเริ่มต้นต้องเป็น 3 ตัวอักษร',
            'default_currency.regex' => 'สกุลเงินเริ่มต้นต้องเป็นตัวอักษรพิมพ์ใหญ่ 3 ตัว เช่น THB, USD',
            
            'date_format.in' => 'รูปแบบวันที่ไม่ถูกต้อง',
            
            'number_format.in' => 'รูปแบบตัวเลขไม่ถูกต้อง',
            
            'start_of_week.in' => 'วันเริ่มต้นสัปดาห์ไม่ถูกต้อง',
            
            'fiscal_year_start.integer' => 'เดือนเริ่มต้นปีงบประมาณต้องเป็นตัวเลข',
            'fiscal_year_start.min' => 'เดือนเริ่มต้นปีงบประมาณต้องเป็น 1-12',
            'fiscal_year_start.max' => 'เดือนเริ่มต้นปีงบประมาณต้องเป็น 1-12',
            
            'dashboard_widgets.array' => 'รูปแบบการตั้งค่า Widget ไม่ถูกต้อง',
            'dashboard_widgets.*.name.required' => 'กรุณาระบุชื่อ Widget',
            'dashboard_widgets.*.name.max' => 'ชื่อ Widget ต้องไม่เกิน 50 ตัวอักษร',
            'dashboard_widgets.*.order.min' => 'ลำดับ Widget ต้องเป็น 1-20',
            'dashboard_widgets.*.order.max' => 'ลำดับ Widget ต้องเป็น 1-20',
            
            'notification_settings.array' => 'รูปแบบการตั้งค่าการแจ้งเตือนไม่ถูกต้อง',
            
            'theme_preference.in' => 'ธีมที่เลือกไม่ถูกต้อง',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'default_account_id' => 'บัญชีเริ่มต้น',
            'default_currency' => 'สกุลเงินเริ่มต้น',
            'date_format' => 'รูปแบบวันที่',
            'number_format' => 'รูปแบบตัวเลข',
            'start_of_week' => 'วันเริ่มต้นสัปดาห์',
            'fiscal_year_start' => 'เดือนเริ่มต้นปีงบประมาณ',
            'dashboard_widgets' => 'การตั้งค่า Widget',
            'notification_settings' => 'การตั้งค่าการแจ้งเตือน',
            'theme_preference' => 'ธีม'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $defaults = [
            'default_currency' => 'THB',
            'date_format' => 'd/m/Y',
            'number_format' => 'comma_dot',
            'start_of_week' => 'sunday',
            'fiscal_year_start' => 1,
            'theme_preference' => 'light',
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!$this->has($key) || $this->input($key) === null) {
                $this->merge([$key => $defaultValue]);
            }
        }

        // Clean up currency
        if ($this->has('default_currency')) {
            $this->merge([
                'default_currency' => strtoupper(trim($this->input('default_currency')))
            ]);
        }

        // Validate and normalize dashboard widgets
        if ($this->has('dashboard_widgets')) {
            $widgets = $this->normalizeDashboardWidgets($this->input('dashboard_widgets'));
            $this->merge(['dashboard_widgets' => $widgets]);
        }

        // Validate and normalize notification settings
        if ($this->has('notification_settings')) {
            $settings = $this->normalizeNotificationSettings($this->input('notification_settings'));
            $this->merge(['notification_settings' => $settings]);
        }
    }

    /**
     * Normalize dashboard widgets
     */
    private function normalizeDashboardWidgets($widgets): array
    {
        if (!is_array($widgets)) {
            return $this->getDefaultWidgets();
        }

        $allowedWidgets = [
            'account_summary',
            'recent_transactions',
            'monthly_summary',
            'budget_overview',
            'financial_goals',
            'investment_summary',
            'recurring_transactions',
            'expense_chart'
        ];

        $normalized = [];
        $order = 1;

        foreach ($widgets as $widget) {
            if (is_array($widget) && isset($widget['name'])) {
                $name = $widget['name'];
                
                if (in_array($name, $allowedWidgets)) {
                    $normalized[$name] = [
                        'enabled' => $widget['enabled'] ?? true,
                        'order' => $widget['order'] ?? $order++
                    ];
                }
            }
        }

        // Add missing default widgets
        foreach ($allowedWidgets as $widgetName) {
            if (!isset($normalized[$widgetName])) {
                $normalized[$widgetName] = [
                    'enabled' => in_array($widgetName, ['account_summary', 'recent_transactions', 'monthly_summary']),
                    'order' => $order++
                ];
            }
        }

        return $normalized;
    }

    /**
     * Normalize notification settings
     */
    private function normalizeNotificationSettings($settings): array
    {
        if (!is_array($settings)) {
            return $this->getDefaultNotificationSettings();
        }

        $defaults = $this->getDefaultNotificationSettings();
        
        return array_merge($defaults, array_intersect_key($settings, $defaults));
    }

    /**
     * Get default dashboard widgets
     */
    private function getDefaultWidgets(): array
    {
        return [
            'account_summary' => ['enabled' => true, 'order' => 1],
            'recent_transactions' => ['enabled' => true, 'order' => 2],
            'monthly_summary' => ['enabled' => true, 'order' => 3],
            'budget_overview' => ['enabled' => true, 'order' => 4],
            'financial_goals' => ['enabled' => true, 'order' => 5],
            'investment_summary' => ['enabled' => false, 'order' => 6],
            'recurring_transactions' => ['enabled' => false, 'order' => 7],
            'expense_chart' => ['enabled' => false, 'order' => 8],
        ];
    }

    /**
     * Get default notification settings
     */
    private function getDefaultNotificationSettings(): array
    {
        return [
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
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Check if default account is active
            if ($this->input('default_account_id')) {
                $account = \App\Models\Account::find($this->input('default_account_id'));
                if ($account && !$account->is_active) {
                    $validator->errors()->add('default_account_id', 
                        'บัญชีเริ่มต้นที่เลือกไม่ได้ใช้งานอยู่');
                }
            }

            // Validate currency code
            if ($this->input('default_currency')) {
                $currency = $this->input('default_currency');
                $validCurrencies = ['THB', 'USD', 'EUR', 'GBP', 'JPY', 'SGD', 'HKD', 'CNY', 'KRW', 'AUD', 'CAD'];
                
                if (!in_array($currency, $validCurrencies)) {
                    $validator->errors()->add('default_currency', 
                        'สกุลเงินที่ไม่รองรับ สกุลเงินที่รองรับ: ' . implode(', ', $validCurrencies));
                }
            }

            // Validate dashboard widgets order uniqueness
            if ($this->input('dashboard_widgets')) {
                $widgets = $this->input('dashboard_widgets');
                $orders = [];
                
                foreach ($widgets as $name => $widget) {
                    if (isset($widget['order']) && isset($widget['enabled']) && $widget['enabled']) {
                        $order = $widget['order'];
                        
                        if (in_array($order, $orders)) {
                            $validator->errors()->add('dashboard_widgets', 
                                'ลำดับของ Widget ไม่สามารถซ้ำกันได้');
                            break;
                        }
                        
                        $orders[] = $order;
                    }
                }
            }

            // Validate that at least one widget is enabled
            if ($this->input('dashboard_widgets')) {
                $widgets = $this->input('dashboard_widgets');
                $hasEnabledWidget = false;
                
                foreach ($widgets as $widget) {
                    if (isset($widget['enabled']) && $widget['enabled']) {
                        $hasEnabledWidget = true;
                        break;
                    }
                }
                
                if (!$hasEnabledWidget) {
                    $validator->errors()->add('dashboard_widgets', 
                        'ต้องเปิดใช้งาน Widget อย่างน้อย 1 รายการ');
                }
            }

            // Validate notification settings consistency
            if ($this->input('notification_settings')) {
                $settings = $this->input('notification_settings');
                
                // If email notifications are disabled, other email-based notifications should be warned
                if (isset($settings['email_notifications']) && !$settings['email_notifications']) {
                    $emailBasedNotifications = ['budget_exceeded', 'goal_achieved', 'monthly_report'];
                    $hasEmailNotifications = false;
                    
                    foreach ($emailBasedNotifications as $notificationType) {
                        if (isset($settings[$notificationType]) && $settings[$notificationType]) {
                            $hasEmailNotifications = true;
                            break;
                        }
                    }
                    
                    if ($hasEmailNotifications) {
                        $validator->errors()->add('notification_settings.email_notifications', 
                            'การปิดการแจ้งเตือนทางอีเมลจะส่งผลต่อการแจ้งเตือนอื่นๆ ที่เปิดใช้งานอยู่');
                    }
                }
            }

            // Validate fiscal year start makes sense
            if ($this->input('fiscal_year_start')) {
                $fiscalStart = $this->input('fiscal_year_start');
                
                // Warn if using unusual fiscal year start (not 1, 4, 7, 10)
                $commonFiscalStarts = [1, 4, 7, 10]; // Jan, Apr, Jul, Oct
                if (!in_array($fiscalStart, $commonFiscalStarts)) {
                    $validator->errors()->add('fiscal_year_start', 
                        'เดือนเริ่มต้นปีงบประมาณที่แนะนำคือ มกราคม (1), เมษายน (4), กรกฎาคม (7), หรือตุลาคม (10)');
                }
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'ข้อมูลไม่ถูกต้อง',
                    'errors' => $validator->errors()
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
}