<?php
// app/Http/Requests/StoreTransactionRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
                Rule::in(['income', 'expense', 'transfer'])
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
            'transaction_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:50'
            ],
            'location' => [
                'nullable',
                'string',
                'max:255'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'is_recurring' => [
                'nullable',
                'boolean'
            ],
            'recurring_type' => [
                'nullable',
                Rule::requiredIf($this->boolean('is_recurring')),
                'string',
                Rule::in(['daily', 'weekly', 'monthly', 'yearly'])
            ],
            'recurring_end_date' => [
                'nullable',
                'date',
                'after:transaction_date'
            ],
            'tags' => [
                'nullable',
                'array',
                'max:10'
            ],
            'tags.*' => [
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9ก-๙\s\-_]+$/'
            ],
            'attachments' => [
                'nullable',
                'array',
                'max:5'
            ],
            'attachments.*' => [
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
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
            
            'transaction_date.required' => 'กรุณาเลือกวันที่',
            'transaction_date.date' => 'รูปแบบวันที่ไม่ถูกต้อง',
            'transaction_date.before_or_equal' => 'วันที่ต้องไม่เกินวันที่ปัจจุบัน',
            
            'reference_number.max' => 'หมายเลขอ้างอิงต้องไม่เกิน 50 ตัวอักษร',
            
            'location.max' => 'สถานที่ต้องไม่เกิน 255 ตัวอักษร',
            
            'notes.max' => 'หมายเหตุต้องไม่เกิน 2,000 ตัวอักษร',
            
            'recurring_type.required_if' => 'กรุณาเลือกประเภทการทำซ้ำ',
            'recurring_type.in' => 'ประเภทการทำซ้ำไม่ถูกต้อง',
            
            'recurring_end_date.date' => 'รูปแบบวันที่สิ้นสุดไม่ถูกต้อง',
            'recurring_end_date.after' => 'วันที่สิ้นสุดต้องหลังจากวันที่รายการ',
            
            'tags.array' => 'รูปแบบแท็กไม่ถูกต้อง',
            'tags.max' => 'สามารถเพิ่มแท็กได้สูงสุด 10 แท็ก',
            'tags.*.max' => 'แท็กต้องไม่เกิน 50 ตัวอักษร',
            'tags.*.regex' => 'แท็กสามารถมีได้เฉพาะตัวอักษร ตัวเลข เครื่องหมาย - _ และช่องว่าง',
            
            'attachments.array' => 'รูปแบบไฟล์แนบไม่ถูกต้อง',
            'attachments.max' => 'สามารถแนบไฟล์ได้สูงสุด 5 ไฟล์',
            'attachments.*.file' => 'ไฟล์แนบไม่ถูกต้อง',
            'attachments.*.max' => 'ไฟล์แนบต้องไม่เกิน 10 MB',
            'attachments.*.mimes' => 'ประเภทไฟล์ที่อนุญาต: jpg, jpeg, png, pdf, doc, docx, xls, xlsx',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'account_id' => 'บัญชี',
            'category_id' => 'หมวดหมู่',
            'type' => 'ประเภทรายการ',
            'amount' => 'จำนวนเงิน',
            'description' => 'รายละเอียด',
            'transaction_date' => 'วันที่',
            'reference_number' => 'หมายเลขอ้างอิง',
            'location' => 'สถานที่',
            'notes' => 'หมายเหตุ',
            'is_recurring' => 'รายการประจำ',
            'recurring_type' => 'ประเภทการทำซ้ำ',
            'recurring_end_date' => 'วันที่สิ้นสุด',
            'tags' => 'แท็ก',
            'attachments' => 'ไฟล์แนับ'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'is_recurring' => $this->boolean('is_recurring', false),
            'transaction_date' => $this->input('transaction_date') ?: now()->format('Y-m-d'),
        ]);

        // Clean up strings
        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->input('description'))
            ]);
        }

        if ($this->has('reference_number')) {
            $this->merge([
                'reference_number' => trim($this->input('reference_number')) ?: null
            ]);
        }

        // Clean up tags
        if ($this->has('tags')) {
            $tags = array_filter(
                array_map('trim', $this->input('tags')),
                fn($tag) => !empty($tag)
            );
            $this->merge(['tags' => array_unique($tags)]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
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

            // Check if amount exceeds available balance for expense (optional business rule)
            if ($this->input('type') === 'expense' && $this->input('account_id') && $this->input('amount')) {
                $account = \App\Models\Account::find($this->input('account_id'));
                if ($account && $account->type !== 'credit_card') {
                    $availableBalance = $account->current_balance;
                    if ($this->input('amount') > $availableBalance) {
                        $validator->errors()->add('amount', 'จำนวนเงินเกินยอดเงินคงเหลือในบัญชี');
                    }
                }
            }

            // Validate credit card credit limit
            if ($this->input('type') === 'expense' && $this->input('account_id') && $this->input('amount')) {
                $account = \App\Models\Account::find($this->input('account_id'));
                if ($account && $account->type === 'credit_card' && $account->credit_limit) {
                    $currentDebt = abs($account->current_balance); // Current balance is negative for debt
                    $newDebt = $currentDebt + $this->input('amount');
                    if ($newDebt > $account->credit_limit) {
                        $validator->errors()->add('amount', 'จำนวนเงินเกินวงเงินเครดิตที่กำหนด');
                    }
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