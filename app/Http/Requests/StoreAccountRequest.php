<?php
// app/Http/Requests/StoreAccountRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
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
                Rule::unique('accounts', 'name')
                    ->where('user_id', $this->user()->id)
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['cash', 'bank', 'credit_card', 'e_wallet'])
            ],
            'account_number' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9\-\s]*$/'
            ],
            'bank_name' => [
                'nullable',
                'string',
                'max:100',
                'required_if:type,bank'
            ],
            'initial_balance' => [
                'required',
                'numeric',
                'between:-999999999999.99,999999999999.99'
            ],
            'credit_limit' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999.99',
                'required_if:type,credit_card'
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50'
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
            'is_include_in_total' => [
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
            'name.required' => 'กรุณาระบุชื่อบัญชี',
            'name.max' => 'ชื่อบัญชีต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'คุณมีบัญชีชื่อนี้อยู่แล้ว',
            
            'type.required' => 'กรุณาเลือกประเภทบัญชี',
            'type.in' => 'ประเภทบัญชีไม่ถูกต้อง',
            
            'account_number.max' => 'หมายเลขบัญชีต้องไม่เกิน 50 ตัวอักษร',
            'account_number.regex' => 'หมายเลขบัญชีสามารถมีเฉพาะตัวเลข เครื่องหมาย - และช่องว่าง',
            
            'bank_name.max' => 'ชื่อธนาคารต้องไม่เกิน 100 ตัวอักษร',
            'bank_name.required_if' => 'กรุณาระบุชื่อธนาคารสำหรับบัญชีธนาคาร',
            
            'initial_balance.required' => 'กรุณาระบุยอดเงินเริ่มต้น',
            'initial_balance.numeric' => 'ยอดเงินเริ่มต้นต้องเป็นตัวเลข',
            'initial_balance.between' => 'ยอดเงินเริ่มต้นต้องอยู่ในช่วงที่กำหนด',
            
            'credit_limit.numeric' => 'วงเงินเครดิตต้องเป็นตัวเลข',
            'credit_limit.min' => 'วงเงินเครดิตต้องไม่ติดลบ',
            'credit_limit.max' => 'วงเงินเครดิตเกินกว่าที่กำหนด',
            'credit_limit.required_if' => 'กรุณาระบุวงเงินเครดิตสำหรับบัตรเครดิต',
            
            'color.regex' => 'รูปแบบสีไม่ถูกต้อง (ต้องเป็น hex color เช่น #FF0000)',
            
            'icon.max' => 'ไอคอนต้องไม่เกิน 50 ตัวอักษร',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'ชื่อบัญชี',
            'type' => 'ประเภทบัญชี',
            'account_number' => 'หมายเลขบัญชี',
            'bank_name' => 'ชื่อธนาคาร',
            'initial_balance' => 'ยอดเงินเริ่มต้น',
            'credit_limit' => 'วงเงินเครดิต',
            'color' => 'สี',
            'icon' => 'ไอคอน',
            'is_active' => 'สถานะการใช้งาน',
            'is_include_in_total' => 'รวมในยอดรวม'
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
            'is_include_in_total' => $this->boolean('is_include_in_total', true),
            'color' => $this->input('color') ?: $this->getDefaultColor(),
            'icon' => $this->input('icon') ?: $this->getDefaultIcon(),
        ]);

        // Clean up account number
        if ($this->has('account_number')) {
            $this->merge([
                'account_number' => $this->cleanAccountNumber($this->input('account_number'))
            ]);
        }
    }

    /**
     * Get default color based on account type
     */
    private function getDefaultColor(): string
    {
        return match($this->input('type')) {
            'cash' => '#28a745',
            'bank' => '#007bff', 
            'credit_card' => '#dc3545',
            'e_wallet' => '#ffc107',
            default => '#6c757d'
        };
    }

    /**
     * Get default icon based on account type
     */
    private function getDefaultIcon(): string
    {
        return match($this->input('type')) {
            'cash' => 'fas fa-money-bill-wave',
            'bank' => 'fas fa-university',
            'credit_card' => 'fas fa-credit-card',
            'e_wallet' => 'fas fa-mobile-alt',
            default => 'fas fa-wallet'
        };
    }

    /**
     * Clean account number (remove extra spaces and normalize)
     */
    private function cleanAccountNumber(?string $accountNumber): ?string
    {
        if (!$accountNumber) {
            return null;
        }

        return preg_replace('/\s+/', ' ', trim($accountNumber));
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