<?php
// app/Http/Requests/UpdateInvestmentTypeRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvestmentTypeRequest extends FormRequest
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
        $investmentType = $this->route('investment_type');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('investment_types', 'name')->ignore($investmentType->id)
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_]+$/',
                Rule::unique('investment_types', 'code')->ignore($investmentType->id)
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
        ]);

        // Clean up and format code
        if ($this->has('code')) {
            $code = strtoupper(trim($this->input('code')));
            $code = preg_replace('/[^A-Z0-9_]/', '_', $code);
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
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $investmentType = $this->route('investment_type');
            
            // Prevent changing code if there are assets using this type
            if ($this->input('code') !== $investmentType->code) {
                $hasAssets = $investmentType->investmentAssets()->exists();
                if ($hasAssets) {
                    $validator->errors()->add('code', 
                        'ไม่สามารถเปลี่ยนรหัสได้เมื่อมีสินทรัพย์การลงทุนใช้ประเภทนี้อยู่');
                }
            }

            // Validate deactivation
            if ($this->boolean('is_active') === false && $investmentType->is_active) {
                $hasActiveAssets = $investmentType->investmentAssets()
                    ->where('is_active', true)
                    ->exists();
                
                if ($hasActiveAssets) {
                    $validator->errors()->add('is_active', 
                        'ไม่สามารถปิดใช้งานประเภทการลงทุนที่มีสินทรัพย์ที่ใช้งานอยู่');
                }
            }

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

            // Validate reserved codes
            $reservedCodes = ['ADMIN', 'SYSTEM', 'USER', 'TEST', 'DEBUG'];
            if (in_array($code, $reservedCodes)) {
                $validator->errors()->add('code', 'ไม่สามารถใช้รหัสที่จองไว้สำหรับระบบได้');
            }

            // Check if there are assets using this investment type that might be affected
            if ($investmentType->investmentAssets()->count() > 0) {
                $significantChanges = [
                    'name' => $this->input('name') !== $investmentType->name,
                    'code' => $this->input('code') !== $investmentType->code,
                ];

                $hasSignificantChanges = array_filter($significantChanges);
                
                if (!empty($hasSignificantChanges)) {
                    $changesList = implode(', ', array_keys($hasSignificantChanges));
                    $validator->errors()->add('name', 
                        "การเปลี่ยนแปลง {$changesList} อาจส่งผลต่อสินทรัพย์การลงทุนที่มีอยู่");
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