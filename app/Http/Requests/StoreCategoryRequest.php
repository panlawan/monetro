<?php
// app/Http/Requests/StoreCategoryRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
                Rule::unique('categories')
                    ->where('user_id', $this->user()->id)
                    ->where('type', $this->input('type'))
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['income', 'expense'])
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
            'description' => [
                'nullable',
                'string',
                'max:500'
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'กรุณาระบุชื่อหมวดหมู่',
            'name.max' => 'ชื่อหมวดหมู่ต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'คุณมีหมวดหมู่ชื่อนี้อยู่แล้วในประเภทเดียวกัน',
            
            'type.required' => 'กรุณาเลือกประเภทหมวดหมู่',
            'type.in' => 'ประเภทหมวดหมู่ไม่ถูกต้อง',
            
            'color.regex' => 'รูปแบบสีไม่ถูกต้อง (ต้องเป็น hex color เช่น #FF0000)',
            
            'icon.max' => 'ไอคอนต้องไม่เกิน 50 ตัวอักษร',
            'icon.regex' => 'ไอคอนสามารถมีได้เฉพาะตัวอักษร ตัวเลข เครื่องหมาย - _ และช่องว่าง',
            
            'description.max' => 'คำอธิบายต้องไม่เกิน 500 ตัวอักษร',
            
            'sort_order.integer' => 'ลำดับการแสดงผลต้องเป็นตัวเลข',
            'sort_order.min' => 'ลำดับการแสดงผลต้องไม่น้อยกว่า 0',
            'sort_order.max' => 'ลำดับการแสดงผลต้องไม่เกิน 9999',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'ชื่อหมวดหมู่',
            'type' => 'ประเภทหมวดหมู่',
            'color' => 'สี',
            'icon' => 'ไอคอน',
            'description' => 'คำอธิบาย',
            'is_active' => 'สถานะการใช้งาน',
            'sort_order' => 'ลำดับการแสดงผล'
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
            'sort_order' => $this->input('sort_order') ?: $this->getNextSortOrder(),
            'color' => $this->input('color') ?: $this->getDefaultColor(),
            'icon' => $this->input('icon') ?: $this->getDefaultIcon(),
        ]);

        // Clean up name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->input('name'))
            ]);
        }
    }

    /**
     * Get next sort order for this user and type
     */
    private function getNextSortOrder(): int
    {
        $maxSort = \App\Models\Category::where('user_id', $this->user()->id)
                                      ->where('type', $this->input('type'))
                                      ->max('sort_order');
        
        return ($maxSort ?? 0) + 10;
    }

    /**
     * Get default color based on category type
     */
    private function getDefaultColor(): string
    {
        return match($this->input('type')) {
            'income' => '#28a745',
            'expense' => '#dc3545',
            default => '#6c757d'
        };
    }

    /**
     * Get default icon based on category type
     */
    private function getDefaultIcon(): string
    {
        return match($this->input('type')) {
            'income' => 'fas fa-plus-circle',
            'expense' => 'fas fa-minus-circle',
            default => 'fas fa-circle'
        };
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Check if user doesn't exceed category limit (optional business rule)
            $categoryCount = \App\Models\Category::where('user_id', $this->user()->id)
                                                ->where('type', $this->input('type'))
                                                ->count();
            
            if ($categoryCount >= 50) {
                $validator->errors()->add('name', 'คุณสามารถสร้างหมวดหมู่ได้สูงสุด 50 หมวดหมู่ต่อประเภท');
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