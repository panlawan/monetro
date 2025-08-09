<?php
// app/Http/Requests/StoreInvestmentTypeRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvestmentTypeRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('investment_types', 'name')
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_]+$/',
                Rule::unique('investment_types', 'code')
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-_]+$/'
            ],
            'is_active' => [
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
            'name.required' => 'กรุณาระบุชื่อประเภทการลงทุน',
            'name.max' => 'ชื่อประเภทการลงทุนต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'มีประเภทการลงทุนชื่อนี้อยู่แล้ว',
            
            'code.required' => 'กรุณาระบุรหัสประเภทการลงทุน',
            'code.max' => 'รหัสประเภทการลงทุนต้องไม่เกิน 20 ตัวอักษร',
            'code.regex' => 'รหัสประเภทการลงทุนสามารถมีได้เฉพาะตัวอักษรพิมพ์ใหญ่ ตัวเลข และ _',
            'code.unique' => 'มีรหัสประเภทการลงทุนนี้อยู่แล้ว',
            
            'description.max' => 'คำอธิบายต้องไม่เกิน 1,000 ตัวอักษร',
            
            'color.regex' => 'รูปแบบสีไม่ถูกต้อง (ต้องเป็น hex color เช่น #FF0000)',
            
            'icon.max' => 'ไอคอนต้องไม่เกิน 50 ตัวอักษร',
            'icon.regex' => 'ไอคอนสามารถมีได้เฉพาะตัวอักษร ตัวเลข เครื่องหมาย - _ และช่องว่าง',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'ชื่อประเภทการลงทุน',
            'code' => 'รหัสประเภทการลงทุน',
            'description' => 'คำอธิบาย',
            'color' => 'สี',
            'icon' => 'ไอคอน',
            'is_active' => 'สถานะการใช้งาน'
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
            'color' => $this->input('color') ?: $this->getDefaultColor(),
            'icon' => $this->input('icon') ?: $this->getDefaultIcon(),
        ]);

        // Clean up and format code
        if ($this->has('code')) {
            $code = strtoupper(trim($this->input('code')));
            $code = preg_replace('/[^A-Z0-9_]/', '_', $code);
            $this->merge(['code' => $code]);
        }

        // Auto-generate code from name if not provided
        if (!$this->input('code') && $this->input('name')) {
            $code = $this->generateCodeFromName($this->input('name'));
            $this->merge(['code' => $code]);
        }

        // Clean up strings
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->input('name'))
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->input('description')) ?: null
            ]);
        }
    }

    /**
     * Get default color
     */
    private function getDefaultColor(): string
    {
        $name = strtolower($this->input('name') ?? '');
        
        if (str_contains($name, 'หุ้น') || str_contains($name, 'stock')) {
            return '#007bff'; // Blue
        } elseif (str_contains($name, 'ทอง') || str_contains($name, 'gold')) {
            return '#ffd700'; // Gold
        } elseif (str_contains($name, 'crypto') || str_contains($name, 'bitcoin')) {
            return '#f7931a'; // Bitcoin orange
        } elseif (str_contains($name, 'กองทุน') || str_contains($name, 'fund')) {
            return '#28a745'; // Green
        } else {
            return '#6c757d'; // Gray
        }
    }

    /**
     * Get default icon
     */
    private function getDefaultIcon(): string
    {
        $name = strtolower($this->input('name') ?? '');
        
        if (str_contains($name, 'หุ้น') || str_contains($name, 'stock')) {
            return 'fas fa-chart-line';
        } elseif (str_contains($name, 'ทอง') || str_contains($name, 'gold')) {
            return 'fas fa-coins';
        } elseif (str_contains($name, 'crypto') || str_contains($name, 'bitcoin')) {
            return 'fab fa-bitcoin';
        } elseif (str_contains($name, 'กองทุน') || str_contains($name, 'fund')) {
            return 'fas fa-piggy-bank';
        } else {
            return 'fas fa-chart-pie';
        }
    }

    /**
     * Generate code from name
     */
    private function generateCodeFromName(string $name): string
    {
        $name = strtolower($name);
        
        $codeMap = [
            'หุ้น' => 'STOCK',
            'stock' => 'STOCK',
            'ทองคำ' => 'GOLD',
            'ทอง' => 'GOLD',
            'gold' => 'GOLD',
            'กองทุน' => 'FUND',
            'fund' => 'FUND',
            'crypto' => 'CRYPTO',
            'bitcoin' => 'CRYPTO',
            'สกุลเงินดิจิทัล' => 'CRYPTO',
            'bond' => 'BOND',
            'หุ้นกู้' => 'BOND',
            'พันธบัตร' => 'BOND',
            'derivative' => 'DERIVATIVE',
            'อนุพันธ์' => 'DERIVATIVE'
        ];

        foreach ($codeMap as $keyword => $code) {
            if (str_contains($name, $keyword)) {
                return $code;
            }
        }

        // Fallback: create code from first letters
        $words = explode(' ', $name);
        $code = '';
        foreach ($words as $word) {
            if (strlen($word) > 0) {
                $code .= strtoupper($word[0]);
            }
        }
        
        return $code ?: 'INVEST';
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Validate that common investment types use standard codes
            $name = strtolower($this->input('name') ?? '');
            $code = $this->input('code');
            
            $standardMappings = [
                'หุ้น' => 'STOCK',
                'ทองคำ' => 'GOLD',
                'กองทุน' => 'FUND',
                'crypto' => 'CRYPTO'
            ];

            foreach ($standardMappings as $nameKeyword => $standardCode) {
                if (str_contains($name, $nameKeyword) && $code !== $standardCode) {
                    $validator->errors()->add('code', 
                        "สำหรับ {$nameKeyword} แนะนำให้ใช้รหัส {$standardCode}");
                    break;
                }
            }

            // Check if code length is reasonable
            if ($code && strlen($code) < 2) {
                $validator->errors()->add('code', 'รหัสประเภทการลงทุนควรมีอย่างน้อย 2 ตัวอักษร');
            }

            // Prevent creating too many investment types
            $typeCount = \App\Models\InvestmentType::count();
            if ($typeCount >= 20) {
                $validator->errors()->add('name', 'มีประเภทการลงทุนในระบบเกินกว่า 20 ประเภทแล้ว');
            }

            // Validate reserved codes
            $reservedCodes = ['ADMIN', 'SYSTEM', 'USER', 'TEST', 'DEBUG'];
            if (in_array($code, $reservedCodes)) {
                $validator->errors()->add('code', 'ไม่สามารถใช้รหัสที่จองไว้สำหรับระบบได้');
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