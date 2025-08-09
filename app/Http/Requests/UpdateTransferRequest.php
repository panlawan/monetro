<?php
// app/Http/Requests/UpdateTransferRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->is_active && 
               $this->user()->can('update', $this->route('transfer'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $this->user()->id),
                'different:to_account_id'
            ],
            'to_account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $this->user()->id),
                'different:from_account_id'
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999999.99'
            ],
            'fee' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99'
            ],
            'exchange_rate' => [
                'nullable',
                'numeric',
                'min:0.0001',
                'max:999999.9999'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'transfer_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:50'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'from_account_id.required' => 'กรุณาเลือกบัญชีต้นทาง',
            'from_account_id.exists' => 'บัญชีต้นทางไม่ถูกต้อง',
            'from_account_id.different' => 'บัญชีต้นทางและปลายทางต้องไม่เหมือนกัน',
            
            'to_account_id.required' => 'กรุณาเลือกบัญชีปลายทาง',
            'to_account_id.exists' => 'บัญชีปลายทางไม่ถูกต้อง',
            'to_account_id.different' => 'บัญชีปลายทางและต้นทางต้องไม่เหมือนกัน',
            
            'amount.required' => 'กรุณาระบุจำนวนเงิน',
            'amount.numeric' => 'จำนวนเงินต้องเป็นตัวเลข',
            'amount.min' => 'จำนวนเงินต้องมากกว่า 0',
            'amount.max' => 'จำนวนเงินเกินกว่าที่กำหนด',
            
            'fee.numeric' => 'ค่าธรรมเนียมต้องเป็นตัวเลข',
            'fee.min' => 'ค่าธรรมเนียมต้องไม่ติดลบ',
            'fee.max' => 'ค่าธรรมเนียมเกินกว่าที่กำหนด',
            
            'exchange_rate.numeric' => 'อัตราแลกเปลี่ยนต้องเป็นตัวเลข',
            'exchange_rate.min' => 'อัตราแลกเปลี่ยนต้องมากกว่า 0',
            'exchange_rate.max' => 'อัตราแลกเปลี่ยนเกินกว่าที่กำหนด',
            
            'description.max' => 'รายละเอียดต้องไม่เกิน 1,000 ตัวอักษร',
            
            'transfer_date.required' => 'กรุณาเลือกวันที่โอน',
            'transfer_date.date' => 'รูปแบบวันที่ไม่ถูกต้อง',
            'transfer_date.before_or_equal' => 'วันที่โอนต้องไม่เกินวันที่ปัจจุบัน',
            
            'reference_number.max' => 'หมายเลขอ้างอิงต้องไม่เกิน 50 ตัวอักษร',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'from_account_id' => 'บัญชีต้นทาง',
            'to_account_id' => 'บัญชีปลายทาง',
            'amount' => 'จำนวนเงิน',
            'fee' => 'ค่าธรรมเนียม',
            'exchange_rate' => 'อัตราแลกเปลี่ยน',
            'description' => 'รายละเอียด',
            'transfer_date' => 'วันที่โอน',
            'reference_number' => 'หมายเลขอ้างอิง'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'fee' => $this->input('fee') ?: 0,
            'exchange_rate' => $this->input('exchange_rate') ?: 1.0000,
        ]);

        // Clean up strings
        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->input('description')) ?: null
            ]);
        }

        if ($this->has('reference_number')) {
            $this->merge([
                'reference_number' => trim($this->input('reference_number')) ?: null
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $transfer = $this->route('transfer');
            
            // Check if from account is active
            if ($this->input('from_account_id')) {
                $fromAccount = \App\Models\Account::find($this->input('from_account_id'));
                if ($fromAccount && !$fromAccount->is_active) {
                    $validator->errors()->add('from_account_id', 'บัญชีต้นทางไม่ได้ใช้งานอยู่');
                }
            }

            // Check if to account is active
            if ($this->input('to_account_id')) {
                $toAccount = \App\Models\Account::find($this->input('to_account_id'));
                if ($toAccount && !$toAccount->is_active) {
                    $validator->errors()->add('to_account_id', 'บัญชีปลายทางไม่ได้ใช้งานอยู่');
                }
            }

            // Prevent changing accounts if transfer has been processed
            if ($this->input('from_account_id') !== $transfer->from_account_id) {
                $validator->errors()->add('from_account_id', 
                    'ไม่สามารถเปลี่ยนบัญชีต้นทางได้หลังจากโอนแล้ว');
            }

            if ($this->input('to_account_id') !== $transfer->to_account_id) {
                $validator->errors()->add('to_account_id', 
                    'ไม่สามารถเปลี่ยนบัญชีปลายทางได้หลังจากโอนแล้ว');
            }

            // Check balance constraints when changing amount
            $originalAmount = $transfer->amount;
            $originalFee = $transfer->fee;
            $newAmount = $this->input('amount');
            $newFee = $this->input('fee');
            
            if ($newAmount != $originalAmount || $newFee != $originalFee) {
                $fromAccount = \App\Models\Account::find($transfer->from_account_id);
                
                if ($fromAccount && $fromAccount->type !== 'credit_card') {
                    // Calculate the difference needed
                    $originalTotal = $originalAmount + $originalFee;
                    $newTotal = $newAmount + $newFee;
                    $difference = $newTotal - $originalTotal;
                    
                    if ($difference > 0) {
                        // Need more money - check if account has enough additional balance
                        $availableBalance = $fromAccount->current_balance + $originalTotal;
                        if ($newTotal > $availableBalance) {
                            $validator->errors()->add('amount', 
                                'ยอดเงินในบัญชีต้นทางไม่เพียงพอสำหรับการเปลี่ยนแปลง');
                        }
                    }
                }
            }

            // Check credit card limits for changes
            if ($newAmount != $originalAmount || $newFee != $originalFee) {
                $fromAccount = \App\Models\Account::find($transfer->from_account_id);
                
                if ($fromAccount && $fromAccount->type === 'credit_card' && $fromAccount->credit_limit) {
                    $originalTotal = $originalAmount + $originalFee;
                    $newTotal = $newAmount + $newFee;
                    $difference = $newTotal - $originalTotal;
                    
                    if ($difference > 0) {
                        $currentDebt = abs($fromAccount->current_balance) - $originalTotal;
                        $newDebt = $currentDebt + $newTotal;
                        
                        if ($newDebt > $fromAccount->credit_limit) {
                            $validator->errors()->add('amount', 
                                'การเปลี่ยนแปลงจะทำให้เกินวงเงินเครดิตของบัญชีต้นทาง');
                        }
                    }
                }
            }

            // Validate fee is reasonable (not more than 50% of transfer amount)
            if ($this->input('amount') && $this->input('fee')) {
                $feePercentage = ($this->input('fee') / $this->input('amount')) * 100;
                if ($feePercentage > 50) {
                    $validator->errors()->add('fee', 
                        'ค่าธรรมเนียมไม่ควรเกิน 50% ของจำนวนเงินที่โอน');
                }
            }

            // Validate exchange rate is reasonable (between 0.01 and 1000)
            if ($this->input('exchange_rate')) {
                $rate = $this->input('exchange_rate');
                if ($rate < 0.01 || $rate > 1000) {
                    $validator->errors()->add('exchange_rate', 
                        'อัตราแลกเปลี่ยนไม่อยู่ในช่วงที่สมเหตุสมผล');
                }
            }

            // Check if transfer date changed significantly
            $originalDate = $transfer->transfer_date;
            $newDate = \Carbon\Carbon::parse($this->input('transfer_date'));
            $daysDiff = abs($originalDate->diffInDays($newDate));
            
            if ($daysDiff > 30) {
                $validator->errors()->add('transfer_date', 
                    'ไม่ควรเปลี่ยนวันที่โอนมากกว่า 30 วัน');
            }

            // Warn about significant amount changes
            if ($this->input('amount') != $transfer->amount) {
                $changePercent = abs(($this->input('amount') - $transfer->amount) / $transfer->amount) * 100;
                
                if ($changePercent > 50) {
                    $validator->errors()->add('amount', 
                        'การเปลี่ยนแปลงจำนวนเงินมากกว่า 50% (' . 
                        number_format($changePercent, 1) . '%) กรุณาตรวจสอบอีกครั้ง');
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