<?php
// app/Http/Requests/UpdateRecurringTransactionRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecurringTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->is_active && 
               $this->user()->can('update', $this->route('recurring_transaction'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $recurringTransaction = $this->route('recurring_transaction');

        return [
            'template_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('recurring_transactions', 'template_name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($recurringTransaction->id)
            ],
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $this->user()->id)
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id)
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['income', 'expense'])
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999999.99'
            ],
            'description' => [
                'required',
                'string',
                'max:1000'
            ],
            'frequency' => [
                'required',
                'string',
                Rule::in(['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])
            ],
            'interval_value' => [
                'nullable',
                'integer',
                'min:1',
                'max:999'
            ],
            'start_date' => [
                'required',
                'date'
            ],
            'end_date' => [
                'nullable',
                'date',
                'after:start_date'
            ],
            'next_due_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date'
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
            'auto_generate' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'template_name.required' => 'กรุณาระบุชื่อรายการประจำ',
            'template_name.max' => 'ชื่อรายการประจำต้องไม่เกิน 255 ตัวอักษร',
            'template_name.unique' => 'คุณมีรายการประจำชื่อนี้อยู่แล้ว',
            
            'account_id.required' => 'กรุณาเลือกบัญชี',
            'account_id.exists' => 'บัญชีที่เลือกไม่ถูกต้อง',
            
            'category_id.required' => 'กรุณาเลือกหมวดหมู่',
            'category_id.exists' => 'หมวดหมู่ที่เลือกไม่ถูกต้อง',
            
            'type.required' => 'กรุณาเลือกประเภทรายการ',
            'type.in' => 'ประเภทรายการไม่ถูกต้อง',
            
            'amount.required' => 'กรุณาระบุจำนวนเงิน',
            'amount.numeric' => 'จำนวนเงินต้องเป็นตัวเลข',
            'amount.min' => 'จำนวนเงินต้องมากกว่า 0',
            'amount.max' => 'จำนวนเงินเกินกว่าที่กำหนด',
            
            'description.required' => 'กรุณาระบุรายละเอียด',
            'description.max' => 'รายละเอียดต้องไม่เกิน 1,000 ตัวอักษร',
            
            'frequency.required' => 'กรุณาเลือกความถี่',
            'frequency.in' => 'ความถี่ไม่ถูกต้อง',
            
            'interval_value.integer' => 'ช่วงความถี่ต้องเป็นตัวเลขจำนวนเต็ม',
            'interval_value.min' => 'ช่วงความถี่ต้องมากกว่า 0',
            'interval_value.max' => 'ช่วงความถี่เกินกว่าที่กำหนด',
            
            'start_date.required' => 'กรุณาเลือกวันที่เริ่มต้น',
            'start_date.date' => 'รูปแบบวันที่เริ่มต้นไม่ถูกต้อง',
            
            'end_date.date' => 'รูปแบบวันที่สิ้นสุดไม่ถูกต้อง',
            'end_date.after' => 'วันที่สิ้นสุดต้องหลังจากวันที่เริ่มต้น',
            
            'next_due_date.date' => 'รูปแบบวันที่ครบกำหนดถัดไปไม่ถูกต้อง',
            'next_due_date.after_or_equal' => 'วันที่ครบกำหนดถัดไปต้องไม่ก่อนวันที่เริ่มต้น',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'template_name' => 'ชื่อรายการประจำ',
            'account_id' => 'บัญชี',
            'category_id' => 'หมวดหมู่',
            'type' => 'ประเภทรายการ',
            'amount' => 'จำนวนเงิน',
            'description' => 'รายละเอียด',
            'frequency' => 'ความถี่',
            'interval_value' => 'ช่วงความถี่',
            'start_date' => 'วันที่เริ่มต้น',
            'end_date' => 'วันที่สิ้นสุด',
            'next_due_date' => 'วันที่ครบกำหนดถัดไป',
            'is_active' => 'สถานะการใช้งาน',
            'auto_generate' => 'สร้างอัตโนมัติ'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'auto_generate' => $this->boolean('auto_generate', false),
            'interval_value' => $this->input('interval_value') ?: 1,
        ]);

        // Clean up strings
        if ($this->has('template_name')) {
            $this->merge([
                'template_name' => trim($this->input('template_name'))
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->input('description'))
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $recurringTransaction = $this->route('recurring_transaction');
            
            // Check if category type matches transaction type
            if ($this->input('category_id') && $this->input('type')) {
                $category = \App\Models\Category::find($this->input('category_id'));
                if ($category && $category->type !== $this->input('type')) {
                    $validator->errors()->add('category_id', 'หมวดหมู่ที่เลือกไม่ตรงกับประเภทรายการ');
                }
            }

            // Check if account is active
            if ($this->input('account_id')) {
                $account = \App\Models\Account::find($this->input('account_id'));
                if ($account && !$account->is_active) {
                    $validator->errors()->add('account_id', 'บัญชีที่เลือกไม่ได้ใช้งานอยู่');
                }
            }

            // Check if category is active
            if ($this->input('category_id')) {
                $category = \App\Models\Category::find($this->input('category_id'));
                if ($category && !$category->is_active) {
                    $validator->errors()->add('category_id', 'หมวดหมู่ที่เลือกไม่ได้ใช้งานอยู่');
                }
            }

            // Validate reasonable interval values for each frequency
            $frequency = $this->input('frequency');
            $intervalValue = $this->input('interval_value');
            
            if ($frequency && $intervalValue) {
                $maxIntervals = [
                    'daily' => 365,      // Max once per day for 365 days
                    'weekly' => 52,      // Max once per week for 52 weeks
                    'monthly' => 12,     // Max once per month for 12 months
                    'quarterly' => 4,    // Max once per quarter for 4 quarters
                    'yearly' => 10       // Max once per year for 10 years
                ];

                if (isset($maxIntervals[$frequency]) && $intervalValue > $maxIntervals[$frequency]) {
                    $validator->errors()->add('interval_value', 
                        "ช่วงความถี่สำหรับ {$frequency} ไม่ควรเกิน {$maxIntervals[$frequency]}");
                }
            }

            // Validate end date is reasonable
            if ($this->input('start_date') && $this->input('end_date')) {
                $startDate = \Carbon\Carbon::parse($this->input('start_date'));
                $endDate = \Carbon\Carbon::parse($this->input('end_date'));
                $yearsDiff = $startDate->diffInYears($endDate);
                
                if ($yearsDiff > 50) {
                    $validator->errors()->add('end_date', 
                        'วันที่สิ้นสุดไม่ควรเกิน 50 ปีจากวันที่เริ่มต้น');
                }
            }

            // Validate that auto_generate is reasonable for the frequency
            if ($this->boolean('auto_generate') && $frequency === 'daily') {
                $validator->errors()->add('auto_generate', 
                    'ไม่แนะนำให้ใช้การสร้างอัตโนมัติสำหรับรายการประจำรายวัน');
            }

            // Check for potential duplicate recurring transactions (exclude current one)
            if ($this->input('template_name') && 
                $this->input('account_id') && 
                $this->input('category_id') && 
                $this->input('amount')) {
                
                $duplicate = \App\Models\RecurringTransaction::where('user_id', $this->user()->id)
                    ->where('id', '!=', $recurringTransaction->id)
                    ->where('account_id', $this->input('account_id'))
                    ->where('category_id', $this->input('category_id'))
                    ->where('amount', $this->input('amount'))
                    ->where('frequency', $frequency)
                    ->where('is_active', true)
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add('template_name', 
                        'คุณมีรายการประจำที่คล้ายกันอยู่แล้ว');
                }
            }

            // Check if deactivating recurring transaction that has generated recent transactions
            if ($this->boolean('is_active') === false && $recurringTransaction->is_active) {
                $recentTransactions = \App\Models\Transaction::where('parent_transaction_id', $recurringTransaction->id)
                    ->where('transaction_date', '>=', now()->subMonth())
                    ->exists();

                if ($recentTransactions) {
                    $validator->errors()->add('is_active', 
                        'มีรายการที่สร้างจากรายการประจำนี้ในเดือนที่ผ่านมา ควรระมัดระวังในการปิดใช้งาน');
                }
            }

            // Validate frequency/interval changes for active recurring transactions
            if ($recurringTransaction->is_active) {
                $frequencyChanged = $this->input('frequency') !== $recurringTransaction->frequency;
                $intervalChanged = $this->input('interval_value') !== $recurringTransaction->interval_value;
                
                if ($frequencyChanged || $intervalChanged) {
                    $validator->errors()->add('frequency', 
                        'การเปลี่ยนความถี่ของรายการประจำที่ใช้งานอยู่อาจส่งผลต่อการสร้างรายการในอนาคต');
                }
            }

            // Recalculate next due date if frequency or start date changed
            if ($this->input('frequency') !== $recurringTransaction->frequency || 
                $this->input('start_date') !== $recurringTransaction->start_date->format('Y-m-d')) {
                
                $nextDueDate = $this->calculateNextDueDate();
                $this->merge(['next_due_date' => $nextDueDate->format('Y-m-d')]);
            }
        });
    }

    /**
     * Calculate next due date based on current settings
     */
    private function calculateNextDueDate(): \Carbon\Carbon
    {
        $startDate = \Carbon\Carbon::parse($this->input('start_date'));
        $frequency = $this->input('frequency');
        $intervalValue = $this->input('interval_value') ?: 1;
        $now = now();

        // If start date is in the future, use start date
        if ($startDate->isFuture()) {
            return $startDate;
        }

        // Calculate next occurrence from now
        $nextDate = $now->copy();
        
        switch ($frequency) {
            case 'daily':
                $nextDate->addDays($intervalValue);
                break;
            case 'weekly':
                $nextDate->addWeeks($intervalValue);
                break;
            case 'monthly':
                $nextDate->addMonths($intervalValue);
                break;
            case 'quarterly':
                $nextDate->addMonths($intervalValue * 3);
                break;
            case 'yearly':
                $nextDate->addYears($intervalValue);
                break;
        }

        return $nextDate;
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