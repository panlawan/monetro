<?php
// app/Http/Requests/StoreDividendRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDividendRequest extends FormRequest
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
            'units_held' => [
                'required',
                'numeric',
                'min:0.000001',
                'max:999999999999.999999'
            ],
            'dividend_per_unit' => [
                'required',
                'numeric',
                'min:0.0001',
                'max:9999999999.9999'
            ],
            'gross_dividend' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999999.99'
            ],
            'tax_withheld' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99'
            ],
            'net_dividend' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999999.99'
            ],
            'record_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'payment_date' => [
                'required',
                'date',
                'after_or_equal:record_date',
                'before_or_equal:today'
            ],
            'dividend_type' => [
                'required',
                'string',
                Rule::in(['cash', 'stock', 'both'])
            ],
            'stock_dividend_units' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999.999999',
                'required_if:dividend_type,stock,both'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000'
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
            
            'account_id.required' => 'กรุณาเลือกบัญชีที่รับเงินปันผล',
            'account_id.exists' => 'บัญชีที่เลือกไม่ถูกต้อง',
            
            'units_held.required' => 'กรุณาระบุจำนวนหน่วยที่ถือ',
            'units_held.numeric' => 'จำนวนหน่วยที่ถือต้องเป็นตัวเลข',
            'units_held.min' => 'จำนวนหน่วยที่ถือต้องมากกว่า 0',
            'units_held.max' => 'จำนวนหน่วยที่ถือเกินกว่าที่กำหนด',
            
            'dividend_per_unit.required' => 'กรุณาระบุปันผลต่อหน่วย',
            'dividend_per_unit.numeric' => 'ปันผลต่อหน่วยต้องเป็นตัวเลข',
            'dividend_per_unit.min' => 'ปันผลต่อหน่วยต้องมากกว่า 0',
            'dividend_per_unit.max' => 'ปันผลต่อหน่วยเกินกว่าที่กำหนด',
            
            'gross_dividend.required' => 'กรุณาระบุปันผลก่อนหักภาษี',
            'gross_dividend.numeric' => 'ปันผลก่อนหักภาษีต้องเป็นตัวเลข',
            'gross_dividend.min' => 'ปันผลก่อนหักภาษีต้องมากกว่า 0',
            'gross_dividend.max' => 'ปันผลก่อนหักภาษีเกินกว่าที่กำหนด',
            
            'tax_withheld.numeric' => 'ภาษีหัก ณ ที่จ่ายต้องเป็นตัวเลข',
            'tax_withheld.min' => 'ภาษีหัก ณ ที่จ่ายต้องไม่ติดลบ',
            'tax_withheld.max' => 'ภาษีหัก ณ ที่จ่ายเกินกว่าที่กำหนด',
            
            'net_dividend.required' => 'กรุณาระบุปันผลสุทธิ',
            'net_dividend.numeric' => 'ปันผลสุทธิต้องเป็นตัวเลข',
            'net_dividend.min' => 'ปันผลสุทธิต้องมากกว่า 0',
            'net_dividend.max' => 'ปันผลสุทธิเกินกว่าที่กำหนด',
            
            'record_date.required' => 'กรุณาเลือกวันปิดสมุด',
            'record_date.date' => 'รูปแบบวันปิดสมุดไม่ถูกต้อง',
            'record_date.before_or_equal' => 'วันปิดสมุดต้องไม่เกินวันที่ปัจจุบัน',
            
            'payment_date.required' => 'กรุณาเลือกวันจ่ายปันผล',
            'payment_date.date' => 'รูปแบบวันจ่ายปันผลไม่ถูกต้อง',
            'payment_date.after_or_equal' => 'วันจ่ายปันผลต้องไม่ก่อนวันปิดสมุด',
            'payment_date.before_or_equal' => 'วันจ่ายปันผลต้องไม่เกินวันที่ปัจจุบัน',
            
            'dividend_type.required' => 'กรุณาเลือกประเภทปันผล',
            'dividend_type.in' => 'ประเภทปันผลไม่ถูกต้อง',
            
            'stock_dividend_units.numeric' => 'หน่วยหุ้นปันผลต้องเป็นตัวเลข',
            'stock_dividend_units.min' => 'หน่วยหุ้นปันผลต้องไม่ติดลบ',
            'stock_dividend_units.max' => 'หน่วยหุ้นปันผลเกินกว่าที่กำหนด',
            'stock_dividend_units.required_if' => 'กรุณาระบุหน่วยหุ้นปันผลสำหรับประเภทนี้',
            
            'notes.max' => 'หมายเหตุต้องไม่เกิน 2,000 ตัวอักษร',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'asset_id' => 'สินทรัพย์การลงทุน',
            'account_id' => 'บัญชีรับเงิน',
            'units_held' => 'จำนวนหน่วยที่ถือ',
            'dividend_per_unit' => 'ปันผลต่อหน่วย',
            'gross_dividend' => 'ปันผลก่อนหักภาษี',
            'tax_withheld' => 'ภาษีหัก ณ ที่จ่าย',
            'net_dividend' => 'ปันผลสุทธิ',
            'record_date' => 'วันปิดสมุด',
            'payment_date' => 'วันจ่ายปันผล',
            'dividend_type' => 'ประเภทปันผล',
            'stock_dividend_units' => 'หน่วยหุ้นปันผล',
            'notes' => 'หมายเหตุ'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'tax_withheld' => $this->input('tax_withheld') ?: 0,
            'stock_dividend_units' => $this->input('stock_dividend_units') ?: 0,
        ]);

        // Calculate values automatically
        if ($this->has('units_held') && $this->has('dividend_per_unit')) {
            $grossDividend = $this->input('units_held') * $this->input('dividend_per_unit');
            $this->merge(['gross_dividend' => $grossDividend]);
        }

        if ($this->has('gross_dividend') && $this->has('tax_withheld')) {
            $netDividend = $this->input('gross_dividend') - $this->input('tax_withheld');
            $this->merge(['net_dividend' => max(0, $netDividend)]);
        }

        // Clean up notes
        if ($this->has('notes')) {
            $this->merge([
                'notes' => trim($this->input('notes')) ?: null
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
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

            // Validate dividend calculations
            if ($this->input('units_held') && $this->input('dividend_per_unit') && $this->input('gross_dividend')) {
                $calculatedGross = $this->input('units_held') * $this->input('dividend_per_unit');
                $tolerance = 0.01; // Allow 1 cent tolerance
                
                if (abs($calculatedGross - $this->input('gross_dividend')) > $tolerance) {
                    $validator->errors()->add('gross_dividend', 
                        'ปันผลก่อนหักภาษีไม่ตรงกับการคำนวณ (ควรเป็น ' . 
                        number_format($calculatedGross, 2) . ' บาท)');
                }
            }

            // Validate net dividend calculation
            if ($this->input('gross_dividend') && $this->input('tax_withheld') && $this->input('net_dividend')) {
                $calculatedNet = $this->input('gross_dividend') - $this->input('tax_withheld');
                $tolerance = 0.01;
                
                if (abs($calculatedNet - $this->input('net_dividend')) > $tolerance) {
                    $validator->errors()->add('net_dividend', 
                        'ปันผลสุทธิไม่ตรงกับการคำนวณ (ควรเป็น ' . 
                        number_format($calculatedNet, 2) . ' บาท)');
                }
            }

            // Validate tax doesn't exceed gross dividend
            if ($this->input('tax_withheld') && $this->input('gross_dividend')) {
                if ($this->input('tax_withheld') > $this->input('gross_dividend')) {
                    $validator->errors()->add('tax_withheld', 
                        'ภาษีหัก ณ ที่จ่ายต้องไม่เกินปันผลก่อนหักภาษี');
                }
            }

            // Check if units held doesn't exceed actual holdings
            if ($this->input('asset_id') && $this->input('units_held')) {
                $asset = \App\Models\InvestmentAsset::find($this->input('asset_id'));
                if ($asset && $this->input('units_held') > $asset->total_units) {
                    $validator->errors()->add('units_held', 
                        'จำนวนหน่วยที่ถือเกินจำนวนหน่วยที่มีจริง (' . 
                        number_format($asset->total_units, 6) . ' หน่วย)');
                }
            }

            // Validate duplicate dividend record
            if ($this->input('asset_id') && $this->input('record_date')) {
                $duplicate = \App\Models\Dividend::where('user_id', $this->user()->id)
                    ->where('asset_id', $this->input('asset_id'))
                    ->where('record_date', $this->input('record_date'))
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add('record_date', 
                        'มีการบันทึกปันผลของสินทรัพย์นี้ในวันปิดสมุดนี้แล้ว');
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