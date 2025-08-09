<?php
// app/Http/Requests/UpdateCategoryRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->is_active && 
               $this->user()->can('update', $this->route('category'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')
                    ->where('user_id', $this->user()->id)
                    ->where('type', $this->input('type') ?: $category->type)
                    ->ignore($category->id)
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
        ]);

        // Clean up name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->input('name'))
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $category = $this->route('category');
            
            // Check if trying to change type when category has transactions
            if ($this->input('type') !== $category->type) {
                $hasTransactions = $category->transactions()->exists();
                if ($hasTransactions) {
                    $validator->errors()->add('type', 'ไม่สามารถเปลี่ยนประเภทหมวดหมู่ที่มีรายการธุรกรรมแล้ว');
                }
            }

            // Check if trying to deactivate category that has active recurring transactions
            if ($this->boolean('is_active') === false) {
                $hasActiveRecurring = $category->recurringTransactions()
                                              ->where('is_active', true)
                                              ->exists();
                if ($hasActiveRecurring) {
                    $validator->errors()->add('is_active', 'ไม่สามารถปิดใช้งานหมวดหมู่ที่มีรายการประจำที่ยังใช้งานอยู่');
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