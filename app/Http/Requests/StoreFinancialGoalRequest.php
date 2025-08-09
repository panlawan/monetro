<?php
// app/Http/Requests/StoreFinancialGoalRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancialGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('financial_goals', 'title')->where('user_id', $this->user()->id)
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'target_amount' => [
                'required',
                'numeric',
                'min:1',
                'max:999999999999.99'
            ],
            'current_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999.99'
            ],
            'monthly_contribution' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99'
            ],
            'target_date' => [
                'nullable',
                'date',
                'after:today'
            ],
            'category' => [
                'required',
                'string',
                Rule::in(['emergency', 'retirement', 'investment', 'purchase', 'other'])
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['planning', 'in_progress', 'achieved', 'paused'])
            ],
            'priority' => [
                'nullable',
                'string',
                Rule::in(['low', 'medium', 'high'])
            ],
            'linked_account_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $this->user()->id)
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'กรุณาระบุชื่อเป้าหมาย',
            'title.max' => 'ชื่อเป้าหมายต้องไม่เกิน 255 ตัวอักษร',
            'title.unique' => 'คุณมีเป้าหมายชื่อนี้อยู่แล้ว',
            
            'description.max' => 'คำอธิบายต้องไม่เกิน 2,000 ตัวอักษร',
            
            'target_amount.required' => 'กรุณาระบุเป้าหมายจำนวนเงิน',
            'target_amount.numeric' => 'เป้าหมายจำนวนเงินต้องเป็นตัวเลข',
            'target_amount.min' => 'เป้าหมายจำนวนเงินต้องมากกว่า 0',
            'target_amount.max' => 'เป้าหมายจำนวนเงินเกินกว่าที่กำหนด',
            
            'current_amount.numeric' => 'จำนวนเงินปัจจุบันต้องเป็นตัวเลข',
            'current_amount.min' => 'จำนวนเงินปัจจุบันต้องไม่ติดลบ',
            'current_amount.max' => 'จำนวนเงินปัจจุบันเกินกว่าที่กำหนด',
            
            'monthly_contribution.numeric' => 'เงินออมรายเดือนต้องเป็นตัวเลข',
            'monthly_contribution.min' => 'เงินออมรายเดือนต้องไม่ติดลบ',
            'monthly_contribution.max' => 'เงินออมรายเดือนเกินกว่าที่กำหนด',
            
            'target_date.date' => 'รูปแบบวันที่เป้าหมายไม่ถูกต้อง',
            'target_date.after' => 'วันที่เป้าหมายต้องเป็นวันในอนาคต',
            
            'category.required' => 'กรุณาเลือกประเภทเป้าหมาย',
            'category.in' => 'ประเภทเป้าหมายไม่ถูกต้อง',
            
            'status.in' => 'สถานะเป้าหมายไม่ถูกต้อง',
            
            'priority.in' => 'ระดับความสำคัญไม่ถูกต้อง',
            
            'linked_account_id.exists' => 'บัญชีที่เชื่อมโยงไม่ถูกต้อง',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'ชื่อเป้าหมาย',
            'description' => 'คำอธิบาย',
            'target_amount' => 'เป้าหมายจำนวนเงิน',
            'current_amount' => 'จำนวนเงินปัจจุบัน',
            'monthly_contribution' => 'เงินออมรายเดือน',
            'target_date' => 'วันที่เป้าหมาย',
            'category' => 'ประเภทเป้าหมาย',
            'status' => 'สถานะ',
            'priority' => 'ระดับความสำคัญ',
            'linked_account_id' => 'บัญชีที่เชื่อมโยง'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'current_amount' => $this->input('current_amount') ?: 0,
            'status' => $this->input('status') ?: 'planning',
            'priority' => $this->input('priority') ?: 'medium',
        ]);

        // Clean up strings
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->input('title'))
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->input('description')) ?: null
            ]);
        }

        // Auto-calculate target date if monthly contribution is provided
        if ($this->input('monthly_contribution') && 
            $this->input('target_amount') && 
            $this->input('current_amount') && 
            !$this->has('target_date')) {
            
            $remainingAmount = $this->input('target_amount') - $this->input('current_amount');
            if ($remainingAmount > 0 && $this->input('monthly_contribution') > 0) {
                $monthsNeeded = ceil($remainingAmount / $this->input('monthly_contribution'));
                $targetDate = now()->addMonths($monthsNeeded)->format('Y-m-d');
                $this->merge(['target_date' => $targetDate]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Check if current amount doesn't exceed target amount
            if ($this->input('current_amount') && $this->input('target_amount')) {
                if ($this->input('current_amount') > $this->input('target_amount')) {
                    $validator->errors()->add('current_amount', 
                        'จำนวนเงินปัจจุบันต้องไม่เกินเป้าหมาย');
                }
            }

            // Check if linked account is active
            if ($this->input('linked_account_id')) {
                $account = \App\Models\Account::find($this->input('linked_account_id'));
                if ($account && !$account->is_active) {
                    $validator->errors()->add('linked_account_id', 
                        'บัญชีที่เชื่อมโยงไม่ได้ใช้งานอยู่');
                }
            }

            // Validate goal feasibility
            if ($this->input('target_date') && 
                $this->input('monthly_contribution') && 
                $this->input('target_amount') && 
                $this->input('current_amount')) {
                
                $remainingAmount = $this->input('target_amount') - $this->input('current_amount');
                $targetDate = \Carbon\Carbon::parse($this->input('target_date'));
                $monthsAvailable = now()->diffInMonths($targetDate);
                
                if ($monthsAvailable > 0) {
                    $requiredMonthly = $remainingAmount / $monthsAvailable;
                    $providedMonthly = $this->input('monthly_contribution');
                    
                    if ($providedMonthly < $requiredMonthly) {
                        $validator->errors()->add('monthly_contribution', 
                            'เงินออมรายเดือนไม่เพียงพอที่จะบรรลุเป้าหมายในกำหนดเวลา ' .
                            '(ต้องออมอย่างน้อย ' . number_format($requiredMonthly, 2) . ' บาท/เดือน)');
                    }
                }
            }

            // Check reasonable goal limits
            if ($this->input('target_date')) {
                $targetDate = \Carbon\Carbon::parse($this->input('target_date'));
                $yearsFromNow = now()->diffInYears($targetDate);
                
                if ($yearsFromNow > 50) {
                    $validator->errors()->add('target_date', 
                        'วันที่เป้าหมายไม่ควรเกิน 50 ปีในอนาคต');
                }
            }

            // Validate category-specific rules
            $category = $this->input('category');
            $targetAmount = $this->input('target_amount');
            
            if ($category === 'emergency' && $targetAmount) {
                // Emergency fund should be reasonable (3-12 months of expenses)
                $monthlyExpenses = $this->estimateMonthlyExpenses();
                if ($monthlyExpenses > 0) {
                    $minEmergency = $monthlyExpenses * 3;
                    $maxEmergency = $monthlyExpenses * 12;
                    
                    if ($targetAmount < $minEmergency) {
                        $validator->errors()->add('target_amount', 
                            'เงินฉุกเฉินควรมีอย่างน้อย 3 เดือนของค่าใช้จ่าย (' . 
                            number_format($minEmergency, 2) . ' บาท)');
                    } elseif ($targetAmount > $maxEmergency) {
                        $validator->errors()->add('target_amount', 
                            'เงินฉุกเฉินไม่ควรเกิน 12 เดือนของค่าใช้จ่าย (' . 
                            number_format($maxEmergency, 2) . ' บาท)');
                    }
                }
            }

            // Check if user doesn't have too many goals
            $activeGoals = \App\Models\FinancialGoal::where('user_id', $this->user()->id)
                ->whereIn('status', ['planning', 'in_progress'])
                ->count();
            
            if ($activeGoals >= 10) {
                $validator->errors()->add('title', 
                    'คุณมีเป้าหมายที่กำลังดำเนินการอยู่เกินกว่า 10 เป้าหมายแล้ว');
            }
        });
    }

    /**
     * Estimate user's monthly expenses (helper method)
     */
    private function estimateMonthlyExpenses(): float
    {
        $lastMonthExpenses = \App\Models\Transaction::where('user_id', $this->user()->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            ])
            ->sum('amount');

        return $lastMonthExpenses;
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