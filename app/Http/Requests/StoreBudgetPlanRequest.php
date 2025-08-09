<?php
// app/Http/Requests/StoreBudgetPlanRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetPlanRequest extends FormRequest
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
                'max:255',
                Rule::unique('budget_plans', 'name')->where('user_id', $this->user()->id)
            ],
            'period_type' => [
                'required',
                'string',
                Rule::in(['monthly', 'quarterly', 'yearly'])
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date'
            ],
            'total_budget' => [
                'required',
                'numeric',
                'min:1',
                'max:999999999999.99'
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
            'budget_categories' => [
                'nullable',
                'array',
                'min:1'
            ],
            'budget_categories.*.category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('user_id', $this->user()->id)
                    ->where('type', 'expense')
            ],
            'budget_categories.*.allocated_amount' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999999.99'
            ],
            'budget_categories.*.is_flexible' => [
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
            'name.required' => 'กรุณาระบุชื่อแผนงบประมาณ',
            'name.max' => 'ชื่อแผนงบประมาณต้องไม่เกิน 255 ตัวอักษร',
            'name.unique' => 'คุณมีแผนงบประมาณชื่อนี้อยู่แล้ว',
            
            'period_type.required' => 'กรุณาเลือกประเภทงวด',
            'period_type.in' => 'ประเภทงวดไม่ถูกต้อง',
            
            'start_date.required' => 'กรุณาเลือกวันที่เริ่มต้น',
            'start_date.date' => 'รูปแบบวันที่เริ่มต้นไม่ถูกต้อง',
            'start_date.after_or_equal' => 'วันที่เริ่มต้นต้องไม่ก่อนวันที่ปัจจุบัน',
            
            'end_date.required' => 'กรุณาเลือกวันที่สิ้นสุด',
            'end_date.date' => 'รูปแบบวันที่สิ้นสุดไม่ถูกต้อง',
            'end_date.after' => 'วันที่สิ้นสุดต้องหลังจากวันที่เริ่มต้น',
            
            'total_budget.required' => 'กรุณาระบุงบประมาณรวม',
            'total_budget.numeric' => 'งบประมาณรวมต้องเป็นตัวเลข',
            'total_budget.min' => 'งบประมาณรวมต้องมากกว่า 0',
            'total_budget.max' => 'งบประมาณรวมเกินกว่าที่กำหนด',
            
            'budget_categories.array' => 'รูปแบบหมวดหมู่งบประมาณไม่ถูกต้อง',
            'budget_categories.min' => 'ต้องมีหมวดหมู่งบประมาณอย่างน้อย 1 หมวดหมู่',
            
            'budget_categories.*.category_id.required' => 'กรุณาเลือกหมวดหมู่',
            'budget_categories.*.category_id.exists' => 'หมวดหมู่ที่เลือกไม่ถูกต้อง',
            
            'budget_categories.*.allocated_amount.required' => 'กรุณาระบุจำนวนเงินที่จัดสรร',
            'budget_categories.*.allocated_amount.numeric' => 'จำนวนเงินที่จัดสรรต้องเป็นตัวเลข',
            'budget_categories.*.allocated_amount.min' => 'จำนวนเงินที่จัดสรรต้องไม่ติดลบ',
            'budget_categories.*.allocated_amount.max' => 'จำนวนเงินที่จัดสรรเกินกว่าที่กำหนด',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'ชื่อแผนงบประมาณ',
            'period_type' => 'ประเภทงวด',
            'start_date' => 'วันที่เริ่มต้น',
            'end_date' => 'วันที่สิ้นสุด',
            'total_budget' => 'งบประมาณรวม',
            'is_active' => 'สถานะการใช้งาน',
            'budget_categories' => 'หมวดหมู่งบประมาณ',
            'budget_categories.*.category_id' => 'หมวดหมู่',
            'budget_categories.*.allocated_amount' => 'จำนวนเงินที่จัดสรร',
            'budget_categories.*.is_flexible' => 'ยืดหยุ่นได้'
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

        // Set default end date based on period type if not provided
        if ($this->has('start_date') && !$this->has('end_date') && $this->has('period_type')) {
            $startDate = \Carbon\Carbon::parse($this->input('start_date'));
            $endDate = match($this->input('period_type')) {
                'monthly' => $startDate->copy()->endOfMonth(),
                'quarterly' => $startDate->copy()->addMonths(3)->subDay(),
                'yearly' => $startDate->copy()->addYear()->subDay(),
                default => $startDate->copy()->endOfMonth()
            };
            
            $this->merge([
                'end_date' => $endDate->format('Y-m-d')
            ]);
        }

        // Prepare budget categories
        if ($this->has('budget_categories')) {
            $categories = collect($this->input('budget_categories'))
                ->map(function ($category) {
                    return [
                        'category_id' => $category['category_id'] ?? null,
                        'allocated_amount' => $category['allocated_amount'] ?? 0,
                        'is_flexible' => isset($category['is_flexible']) ? 
                            (bool) $category['is_flexible'] : true,
                    ];
                })
                ->filter(function ($category) {
                    return !empty($category['category_id']) && $category['allocated_amount'] > 0;
                })
                ->values()
                ->toArray();

            $this->merge(['budget_categories' => $categories]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Check if user has overlapping budget plans for the same period
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            
            if ($startDate && $endDate) {
                $overlapping = \App\Models\BudgetPlan::where('user_id', $this->user()->id)
                    ->where('is_active', true)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                                $subQuery->where('start_date', '<=', $startDate)
                                        ->where('end_date', '>=', $endDate);
                            });
                    })
                    ->exists();

                if ($overlapping) {
                    $validator->errors()->add('start_date', 'มีแผนงบประมาณที่ใช้งานอยู่ในช่วงเวลาที่ซ้อนทับกัน');
                }
            }

            // Check if total allocated amount doesn't exceed total budget
            if ($this->has('budget_categories') && $this->input('total_budget')) {
                $totalAllocated = collect($this->input('budget_categories'))
                    ->sum('allocated_amount');

                if ($totalAllocated > $this->input('total_budget')) {
                    $validator->errors()->add('total_budget', 
                        'งบประมาณรวมต้องมากกว่าหรือเท่ากับจำนวนเงินที่จัดสรรทั้งหมด (' . 
                        number_format($totalAllocated, 2) . ' บาท)');
                }
            }

            // Check for duplicate categories
            if ($this->has('budget_categories')) {
                $categoryIds = collect($this->input('budget_categories'))
                    ->pluck('category_id')
                    ->filter();

                if ($categoryIds->count() !== $categoryIds->unique()->count()) {
                    $validator->errors()->add('budget_categories', 'ไม่สามารถเลือกหมวดหมู่เดียวกันซ้ำได้');
                }
            }

            // Validate period dates based on period type
            if ($this->input('period_type') && $this->input('start_date') && $this->input('end_date')) {
                $startDate = \Carbon\Carbon::parse($this->input('start_date'));
                $endDate = \Carbon\Carbon::parse($this->input('end_date'));
                $expectedDays = match($this->input('period_type')) {
                    'monthly' => $startDate->daysInMonth,
                    'quarterly' => 90, // Approximate
                    'yearly' => $startDate->isLeapYear() ? 366 : 365,
                    default => null
                };

                if ($expectedDays) {
                    $actualDays = $startDate->diffInDays($endDate) + 1;
                    $tolerance = match($this->input('period_type')) {
                        'monthly' => 3,  // Allow 3 days difference
                        'quarterly' => 10, // Allow 10 days difference  
                        'yearly' => 30,  // Allow 30 days difference
                        default => 0
                    };

                    if (abs($actualDays - $expectedDays) > $tolerance) {
                        $validator->errors()->add('end_date', 
                            'ช่วงเวลาไม่สอดคล้องกับประเภทงวดที่เลือก');
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