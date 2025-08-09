<?php
// app/Http/Requests/UpdateInvestmentAssetRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvestmentAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->is_active && 
               $this->user()->can('update', $this->route('investment_asset'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $asset = $this->route('investment_asset');

        return [
            'investment_type_id' => [
                'required',
                'integer',
                Rule::exists('investment_types', 'id')->where('is_active', true)
            ],
            'symbol' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9\-\.]+$/',
                Rule::unique('investment_assets')
                    ->where('user_id', $this->user()->id)
                    ->ignore($asset->id)
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'market' => [
                'nullable',
                'string',
                'max:50'
            ],
            'currency' => [
                'nullable',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/'
            ],
            'current_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.9999'
            ],
            'last_price_update' => [
                'nullable',
                'date',
                'before_or_equal:now'
            ],
            'is_active' => [
                'nullable',
                'boolean'
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
            'investment_type_id.required' => 'กรุณาเลือกประเภทการลงทุน',
            'investment_type_id.exists' => 'ประเภทการลงทุนที่เลือกไม่ถูกต้อง',
            
            'symbol.required' => 'กรุณาระบุสัญลักษณ์',
            'symbol.max' => 'สัญลักษณ์ต้องไม่เกิน 20 ตัวอักษร',
            'symbol.regex' => 'สัญลักษณ์สามารถมีได้เฉพาะตัวอักษรพิมพ์ใหญ่ ตัวเลข เครื่องหมาย - และ .',
            'symbol.unique' => 'คุณมีสินทรัพย์สัญลักษณ์นี้อยู่แล้ว',
            
            'name.required' => 'กรุณาระบุชื่อสินทรัพย์',
            'name.max' => 'ชื่อสินทรัพย์ต้องไม่เกิน 255 ตัวอักษร',
            
            'market.max' => 'ตลาดการซื้อขายต้องไม่เกิน 50 ตัวอักษร',
            
            'currency.size' => 'สกุลเงินต้องเป็น 3 ตัวอักษร',
            'currency.regex' => 'สกุลเงินต้องเป็นตัวอักษรพิมพ์ใหญ่ 3 ตัว เช่น THB, USD',
            
            'current_price.numeric' => 'ราคาปัจจุบันต้องเป็นตัวเลข',
            'current_price.min' => 'ราคาปัจจุบันต้องไม่ติดลบ',
            'current_price.max' => 'ราคาปัจจุบันเกินกว่าที่กำหนด',
            
            'last_price_update.date' => 'รูปแบบวันที่อัปเดตราคาไม่ถูกต้อง',
            'last_price_update.before_or_equal' => 'วันที่อัปเดตราคาต้องไม่เกินเวลาปัจจุบัน',
            
            'notes.max' => 'หมายเหตุต้องไม่เกิน 2,000 ตัวอักษร',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'investment_type_id' => 'ประเภทการลงทุน',
            'symbol' => 'สัญลักษณ์',
            'name' => 'ชื่อสินทรัพย์',
            'market' => 'ตลาดการซื้อขาย',
            'currency' => 'สกุลเงิน',
            'current_price' => 'ราคาปัจจุบัน',
            'last_price_update' => 'วันที่อัปเดตราคา',
            'is_active' => 'สถานะการใช้งาน',
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
            'is_active' => $this->boolean('is_active', true),
            'currency' => $this->input('currency') ?: 'THB',
        ]);

        // Clean up and format symbol
        if ($this->has('symbol')) {
            $symbol = strtoupper(trim($this->input('symbol')));
            $this->merge(['symbol' => $symbol]);
        }

        // Clean up currency
        if ($this->has('currency')) {
            $currency = strtoupper(trim($this->input('currency')));
            $this->merge(['currency' => $currency]);
        }

        // Clean up strings
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->input('name'))
            ]);
        }

        if ($this->has('market')) {
            $this->merge([
                'market' => trim($this->input('market')) ?: null
            ]);
        }

        if ($this->has('notes')) {
            $this->merge([
                'notes' => trim($this->input('notes')) ?: null
            ]);
        }

        // Update price timestamp if price changed
        $asset = $this->route('investment_asset');
        if ($this->input('current_price') && 
            $this->input('current_price') != $asset->current_price && 
            !$this->has('last_price_update')) {
            $this->merge([
                'last_price_update' => now()->format('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $asset = $this->route('investment_asset');
            
            // Prevent changing investment type if asset has transactions
            if ($this->input('investment_type_id') !== $asset->investment_type_id) {
                $hasTransactions = $asset->investmentTransactions()->exists();
                if ($hasTransactions) {
                    $validator->errors()->add('investment_type_id', 
                        'ไม่สามารถเปลี่ยนประเภทการลงทุนได้เมื่อมีรายการซื้อขายแล้ว');
                }
            }

            // Prevent changing symbol if asset has transactions
            if ($this->input('symbol') !== $asset->symbol) {
                $hasTransactions = $asset->investmentTransactions()->exists() || 
                                 $asset->dividends()->exists();
                if ($hasTransactions) {
                    $validator->errors()->add('symbol', 
                        'ไม่สามารถเปลี่ยนสัญลักษณ์ได้เมื่อมีรายการธุรกรรมแล้ว');
                }
            }

            // Validate symbol format based on investment type
            if ($this->input('investment_type_id') && $this->input('symbol')) {
                $investmentType = \App\Models\InvestmentType::find($this->input('investment_type_id'));
                
                if ($investmentType) {
                    $this->validateSymbolByType($validator, $investmentType->code, $this->input('symbol'));
                }
            }

            // Validate market based on symbol/type
            if ($this->input('symbol') && $this->input('market')) {
                $this->validateMarketCompatibility($validator, $this->input('symbol'), $this->input('market'));
            }

            // Check if current price is reasonable
            if ($this->input('current_price')) {
                $price = $this->input('current_price');
                
                // Compare with previous price for sanity check
                if ($asset->current_price > 0) {
                    $changePercent = abs(($price - $asset->current_price) / $asset->current_price) * 100;
                    
                    if ($changePercent > 50) {
                        $validator->errors()->add('current_price', 
                            'ราคาเปลี่ยนแปลงมากกว่า 50% (' . 
                            number_format($changePercent, 1) . '%) กรุณาตรวจสอบอีกครั้ง');
                    }
                }

                // Very basic sanity check
                if ($price > 1000000) {
                    $validator->errors()->add('current_price', 
                        'ราคาดูสูงผิดปกติ กรุณาตรวจสอบอีกครั้ง');
                } elseif ($price < 0.0001) {
                    $validator->errors()->add('current_price', 
                        'ราคาดูต่ำผิดปกติ กรุณาตรวจสอบอีกครั้ง');
                }
            }

            // Validate deactivation
            if ($this->boolean('is_active') === false && $asset->is_active) {
                $hasActivePositions = $asset->total_units > 0;
                if ($hasActivePositions) {
                    $validator->errors()->add('is_active', 
                        'ไม่สามารถปิดใช้งานสินทรัพย์ที่ยังมีการถือครองอยู่');
                }
            }
        });
    }

    /**
     * Validate symbol format based on investment type
     */
    private function validateSymbolByType($validator, string $typeCode, string $symbol): void
    {
        switch ($typeCode) {
            case 'STOCK':
                // Stock symbols are usually 3-5 characters
                if (strlen($symbol) < 2 || strlen($symbol) > 10) {
                    $validator->errors()->add('symbol', 
                        'สัญลักษณ์หุ้นควรมี 2-10 ตัวอักษร');
                }
                break;
                
            case 'CRYPTO':
                // Crypto symbols are usually 3-5 characters
                if (strlen($symbol) < 2 || strlen($symbol) > 10) {
                    $validator->errors()->add('symbol', 
                        'สัญลักษณ์สกุลเงินดิจิทัลควรมี 2-10 ตัวอักษร');
                }
                break;
                
            case 'FUND':
                // Fund codes can be longer
                if (strlen($symbol) < 3 || strlen($symbol) > 15) {
                    $validator->errors()->add('symbol', 
                        'รหัสกองทุนควรมี 3-15 ตัวอักษร');
                }
                break;
                
            case 'GOLD':
                // Gold symbols are usually short
                if (!in_array($symbol, ['GOLD', 'XAU', 'GLD', 'GOLDF'])) {
                    $validator->errors()->add('symbol', 
                        'สัญลักษณ์ทองคำควรเป็น GOLD, XAU, GLD หรือ GOLDF');
                }
                break;
        }
    }

    /**
     * Validate market compatibility with symbol
     */
    private function validateMarketCompatibility($validator, string $symbol, string $market): void
    {
        $thaiStockPatterns = ['/^[A-Z]{2,4}$/', '/^[A-Z]+F$/']; // Thai stocks
        $usStockPatterns = ['/^[A-Z]{1,5}$/']; // US stocks
        
        // Check if Thai stock symbol is used with non-Thai market
        foreach ($thaiStockPatterns as $pattern) {
            if (preg_match($pattern, $symbol)) {
                if (!in_array(strtoupper($market), ['SET', 'MAI', 'TFEX'])) {
                    $validator->errors()->add('market', 
                        'สัญลักษณ์นี้ดูเหมือนหุ้นไทย ควรใช้ตลาด SET, MAI หรือ TFEX');
                    break;
                }
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