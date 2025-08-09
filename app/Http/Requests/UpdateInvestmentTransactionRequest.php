<?php
// app/Http/Requests/UpdateInvestmentTransactionRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvestmentTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->is_active && 
               $this->user()->can('update', $this->route('investment_transaction'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('investment_assets', 'id')->where('user_id', $this->user()->id)
            ],
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $this->user()->id)
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['buy', 'sell', 'dividend', 'split', 'bonus'])
            ],
            'units' => [
                'required',
                'numeric',
                'min:0.000001',
                'max:999999999999.999999'
            ],
            'price_per_unit' => [
                'required',
                'numeric',
                'min:0.0001',
                'max:9999999999.9999'
            ],
            'total_amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999999.99'
            ],
            'fees' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99'
            ],
            'taxes' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99'
            ],
            'net_amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999999.99'
            ],
            'transaction_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'settlement_date' => [
                'nullable',
                'date',
                'after_or_equal:transaction_date'
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:50'
            ],
            'broker' => [
                'nullable',
                'string',
                'max:100'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'exchange_rate' => [
                'nullable',
                'numeric',
                'min:0.0001',
                'max:999999.9999'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'asset_id.required' => 'กรุณาเลือกสินทรัพย์การลงทุน',
            'asset_id.exists' => 'สินทรัพย์การลงทุนที่เลือกไม่ถูกต้อง',
            
            'account_id.required' => 'กรุณาเลือกบัญชี',
            'account_id.exists' => 'บัญชีที่เลือกไม่ถูกต้อง',
            
            'type.required' => 'กรุณาเลือกประเภทรายการ',
            'type.in' => 'ประเภทรายการไม่ถูกต้อง',
            
            'units.required' => 'กรุณาระบุจำนวนหน่วย',
            'units.numeric' => 'จำนวนหน่วยต้องเป็นตัวเลข',
            'units.min' => 'จำนวนหน่วยต้องมากกว่า 0',
            'units.max' => 'จำนวนหน่วยเกินกว่าที่กำหนด',
            
            'price_per_unit.required' => 'กรุณาระบุราคาต่อหน่วย',
            'price_per_unit.numeric' => 'ราคาต่อหน่วยต้องเป็นตัวเลข',
            'price_per_unit.min' => 'ราคาต่อหน่วยต้องมากกว่า 0',
            'price_per_unit.max' => 'ราคาต่อหน่วยเกินกว่าที่กำหนด',
            
            'total_amount.required' => 'กรุณาระบุมูลค่ารวม',
            'total_amount.numeric' => 'มูลค่ารวมต้องเป็นตัวเลข',
            'total_amount.min' => 'มูลค่ารวมต้องมากกว่า 0',
            'total_amount.max' => 'มูลค่ารวมเกินกว่าที่กำหนด',
            
            'fees.numeric' => 'ค่าธรรมเนียมต้องเป็นตัวเลข',
            'fees.min' => 'ค่าธรรมเนียมต้องไม่ติดลบ',
            'fees.max' => 'ค่าธรรมเนียมเกินกว่าที่กำหนด',
            
            'taxes.numeric' => 'ภาษีต้องเป็นตัวเลข',
            'taxes.min' => 'ภาษีต้องไม่ติดลบ',
            'taxes.max' => 'ภาษีเกินกว่าที่กำหนด',
            
            'net_amount.required' => 'กรุณาระบุจำนวนเงินสุทธิ',
            'net_amount.numeric' => 'จำนวนเงินสุทธิต้องเป็นตัวเลข',
            'net_amount.min' => 'จำนวนเงินสุทธิต้องมากกว่า 0',
            'net_amount.max' => 'จำนวนเงินสุทธิเกินกว่าที่กำหนด',
            
            'transaction_date.required' => 'กรุณาเลือกวันที่ทำรายการ',
            'transaction_date.date' => 'รูปแบบวันที่ทำรายการไม่ถูกต้อง',
            'transaction_date.before_or_equal' => 'วันที่ทำรายการต้องไม่เกินวันที่ปัจจุบัน',
            
            'settlement_date.date' => 'รูปแบบวันที่เซ็ตเติลไม่ถูกต้อง',
            'settlement_date.after_or_equal' => 'วันที่เซ็ตเติลต้องไม่ก่อนวันที่ทำรายการ',
            
            'reference_number.max' => 'หมายเลขอ้างอิงต้องไม่เกิน 50 ตัวอักษร',
            
            'broker.max' => 'ชื่อบริษัทหลักทรัพย์ต้องไม่เกิน 100 ตัวอักษร',
            
            'notes.max' => 'หมายเหตุต้องไม่เกิน 2,000 ตัวอักษร',
            
            'exchange_rate.numeric' => 'อัตราแลกเปลี่ยนต้องเป็นตัวเลข',
            'exchange_rate.min' => 'อัตราแลกเปลี่ยนต้องมากกว่า 0',
            'exchange_rate.max' => 'อัตราแลกเปลี่ยนเกินกว่าที่กำหนด',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'asset_id' => 'สินทรัพย์การลงทุน',
            'account_id' => 'บัญชี',
            'type' => 'ประเภทรายการ',
            'units' => 'จำนวนหน่วย',
            'price_per_unit' => 'ราคาต่อหน่วย',
            'total_amount' => 'มูลค่ารวม',
            'fees' => 'ค่าธรรมเนียม',
            'taxes' => 'ภาษี',
            'net_amount' => 'จำนวนเงินสุทธิ',
            'transaction_date' => 'วันที่ทำรายการ',
            'settlement_date' => 'วันที่เซ็ตเติล',
            'reference_number' => 'หมายเลขอ้างอิง',
            'broker' => 'บริษัทหลักทรัพย์',
            'notes' => 'หมายเหตุ',
            'exchange_rate' => 'อัตราแลกเปลี่ยน'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'fees' => $this->input('fees') ?: 0,
            'taxes' => $this->input('taxes') ?: 0,
            'exchange_rate' => $this->input('exchange_rate') ?: 1.0000,
        ]);

        // Calculate total amount if not provided
        if (!$this->has('total_amount') && $this->has('units') && $this->has('price_per_unit')) {
            $totalAmount = $this->input('units') * $this->input('price_per_unit');
            $this->merge(['total_amount' => $totalAmount]);
        }

        // Calculate net amount if not provided
        if (!$this->has('net_amount') && $this->has('total_amount')) {
            $fees = $this->input('fees') ?: 0;
            $taxes = $this->input('taxes') ?: 0;
            $netAmount = $this->input('total_amount') + $fees + $taxes;
            $this->merge(['net_amount' => $netAmount]);
        }

        // Clean up strings
        $stringFields = ['reference_number', 'broker', 'notes'];
        foreach ($stringFields as $field) {
            if ($this->has($field)) {
                $value = trim($this->input($field));
                $this->merge([$field => $value ?: null]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $transaction = $this->route('investment_transaction');
            
            // Check if asset is active
            if ($this->input('asset_id')) {
                $asset = \App\Models\InvestmentAsset::find($this->input('asset_id'));
                if ($asset && !$asset->is_active) {
                    $validator->errors()->add('asset_id', 'สินทรัพย์การลงทุนที่เลือกไม่ได้ใช้งานอยู่');
                }
            }

            // Check if account is active
            if ($this->input('account_id')) {
                $account = \App\Models\Account::find($this->input('account_id'));
                if ($account && !$account->is_active) {
                    $validator->errors()->add('account_id', 'บัญชีที่เลือกไม่ได้ใช้งานอยู่');
                }
            }

            // Validate calculation consistency
            $this->validateCalculations($validator);

            // Prevent changing transaction type
            if ($this->input('type') !== $transaction->type) {
                $validator->errors()->add('type', 
                    'ไม่สามารถเปลี่ยนประเภทรายการได้หลังจากบันทึกแล้ว');
            }

            // Prevent changing asset
            if ($this->input('asset_id') !== $transaction->asset_id) {
                $validator->errors()->add('asset_id', 
                    'ไม่สามารถเปลี่ยนสินทรัพย์การลงทุนได้หลังจากบันทึกแล้ว');
            }

            // Validate sufficient units for sell orders (considering the change)
            if ($this->input('type') === 'sell' && $this->input('asset_id') && $this->input('units')) {
                $asset = \App\Models\InvestmentAsset::find($this->input('asset_id'));
                $originalUnits = $transaction->units;
                $newUnits = $this->input('units');
                $unitDifference = $newUnits - $originalUnits;
                
                if ($asset && $unitDifference > 0) {
                    $availableUnits = $asset->total_units + $originalUnits; // Add back original units
                    if ($newUnits > $availableUnits) {
                        $validator->errors()->add('units', 
                            'จำนวนหน่วยที่ขายเกินจำนวนที่ถืออยู่ (' . 
                            number_format($availableUnits, 6) . ' หน่วย)');
                    }
                }
            }

            // Validate sufficient balance for buy orders (considering the change)
            if (in_array($this->input('type'), ['buy']) && $this->input('account_id') && $this->input('net_amount')) {
                $account = \App\Models\Account::find($this->input('account_id'));
                $originalNetAmount = $transaction->net_amount;
                $newNetAmount = $this->input('net_amount');
                $amountDifference = $newNetAmount - $originalNetAmount;
                
                if ($account && $account->type !== 'credit_card' && $amountDifference > 0) {
                    $availableBalance = $account->current_balance + $originalNetAmount; // Add back original amount
                    if ($newNetAmount > $availableBalance) {
                        $validator->errors()->add('net_amount', 
                            'ยอดเงินในบัญชีไม่เพียงพอสำหรับการเปลี่ยนแปลง');
                    }
                }
            }

            // Validate reasonable fees and taxes
            if ($this->input('total_amount') && ($this->input('fees') || $this->input('taxes'))) {
                $totalAmount = $this->input('total_amount');
                $fees = $this->input('fees') ?: 0;
                $taxes = $this->input('taxes') ?: 0;
                
                $feePercentage = ($fees / $totalAmount) * 100;
                $taxPercentage = ($taxes / $totalAmount) * 100;
                
                if ($feePercentage > 10) {
                    $validator->errors()->add('fees', 
                        'ค่าธรรมเนียมสูงผิดปกติ (' . number_format($feePercentage, 2) . '%)');
                }
                
                if ($taxPercentage > 30) {
                    $validator->errors()->add('taxes', 
                        'ภาษีสูงผิดปกติ (' . number_format($taxPercentage, 2) . '%)');
                }
            }

            // Validate business day for settlement
            if ($this->input('settlement_date')) {
                $settlementDate = \Carbon\Carbon::parse($this->input('settlement_date'));
                if ($settlementDate->isWeekend()) {
                    $validator->errors()->add('settlement_date', 
                        'วันที่เซ็ตเติลไม่ควรเป็นวันหยุดสุดสัปดาห์');
                }
            }

            // Check if transaction date changed significantly
            $originalDate = $transaction->transaction_date;
            $newDate = \Carbon\Carbon::parse($this->input('transaction_date'));
            $daysDiff = abs($originalDate->diffInDays($newDate));
            
            if ($daysDiff > 30) {
                $validator->errors()->add('transaction_date', 
                    'ไม่ควรเปลี่ยนวันที่ทำรายการมากกว่า 30 วัน');
            }
        });
    }

    /**
     * Validate calculation consistency
     */
    private function validateCalculations($validator): void
    {
        if (!$this->input('units') || !$this->input('price_per_unit') || !$this->input('total_amount')) {
            return;
        }

        $units = $this->input('units');
        $pricePerUnit = $this->input('price_per_unit');
        $totalAmount = $this->input('total_amount');
        $calculatedTotal = $units * $pricePerUnit;
        
        $tolerance = 0.01; // Allow 1 cent tolerance
        
        if (abs($calculatedTotal - $totalAmount) > $tolerance) {
            $validator->errors()->add('total_amount', 
                'มูลค่ารวมไม่ตรงกับการคำนวณ (ควรเป็น ' . 
                number_format($calculatedTotal, 2) . ' บาท)');
        }

        // Validate net amount calculation
        if ($this->input('net_amount')) {
            $fees = $this->input('fees') ?: 0;
            $taxes = $this->input('taxes') ?: 0;
            $type = $this->input('type');
            
            $calculatedNet = match($type) {
                'buy' => $totalAmount + $fees + $taxes,
                'sell' => $totalAmount - $fees - $taxes,
                'dividend' => $totalAmount - $taxes,
                default => $totalAmount
            };
            
            if (abs($calculatedNet - $this->input('net_amount')) > $tolerance) {
                $validator->errors()->add('net_amount', 
                    'จำนวนเงินสุทธิไม่ตรงกับการคำนวณ (ควรเป็น ' . 
                    number_format($calculatedNet, 2) . ' บาท)');
            }
        }
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