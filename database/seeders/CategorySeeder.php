<?php
// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\User;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Default categories for all users
        $defaultCategories = [
            // Income Categories
            [
                'name' => 'เงินเดือน',
                'type' => 'income',
                'color' => '#1cc88a',
                'icon' => 'fa-money-bill-wave',
                'description' => 'รายได้จากการทำงาน',
                'sort_order' => 1
            ],
            [
                'name' => 'ธุรกิจส่วนตัว',
                'type' => 'income',
                'color' => '#36b9cc',
                'icon' => 'fa-store',
                'description' => 'รายได้จากธุรกิจส่วนตัว',
                'sort_order' => 2
            ],
            [
                'name' => 'เงินลงทุน',
                'type' => 'income',
                'color' => '#f6c23e',
                'icon' => 'fa-chart-line',
                'description' => 'รายได้จากการลงทุน',
                'sort_order' => 3
            ],
            [
                'name' => 'รายได้อื่นๆ',
                'type' => 'income',
                'color' => '#858796',
                'icon' => 'fa-coins',
                'description' => 'รายได้อื่นๆ',
                'sort_order' => 4
            ],

            // Expense Categories
            [
                'name' => 'อาหาร',
                'type' => 'expense',
                'color' => '#e74a3b',
                'icon' => 'fa-utensils',
                'description' => 'ค่าอาหารและเครื่องดื่ม',
                'sort_order' => 1
            ],
            [
                'name' => 'คมนาคม',
                'type' => 'expense',
                'color' => '#f39c12',
                'icon' => 'fa-car',
                'description' => 'ค่าเดินทางและคมนาคม',
                'sort_order' => 2
            ],
            [
                'name' => 'ที่พัก',
                'type' => 'expense',
                'color' => '#9b59b6',
                'icon' => 'fa-home',
                'description' => 'ค่าเช่าบ้าน ค่าน้ำ ค่าไฟ',
                'sort_order' => 3
            ],
            [
                'name' => 'สุขภาพ',
                'type' => 'expense',
                'color' => '#2ecc71',
                'icon' => 'fa-heartbeat',
                'description' => 'ค่ารักษาพยาบาล ค่ายา',
                'sort_order' => 4
            ],
            [
                'name' => 'ศึกษา',
                'type' => 'expense',
                'color' => '#3498db',
                'icon' => 'fa-graduation-cap',
                'description' => 'ค่าเรียน หนังสือ อุปกรณ์การศึกษา',
                'sort_order' => 5
            ],
            [
                'name' => 'ซื้อของ',
                'type' => 'expense',
                'color' => '#e67e22',
                'icon' => 'fa-shopping-cart',
                'description' => 'เสื้อผ้า ของใช้ต่างๆ',
                'sort_order' => 6
            ],
            [
                'name' => 'บันเทิง',
                'type' => 'expense',
                'color' => '#f1c40f',
                'icon' => 'fa-gamepad',
                'description' => 'ภาพยนตร์ เกม กิจกรรมบันเทิง',
                'sort_order' => 7
            ],
            [
                'name' => 'ออมเงิน',
                'type' => 'expense',
                'color' => '#1abc9c',
                'icon' => 'fa-piggy-bank',
                'description' => 'เงินออม เงินลงทุน',
                'sort_order' => 8
            ],
            [
                'name' => 'อื่นๆ',
                'type' => 'expense',
                'color' => '#95a5a6',
                'icon' => 'fa-ellipsis-h',
                'description' => 'รายจ่ายอื่นๆ',
                'sort_order' => 9
            ]
        ];

        // Create categories for each user
        User::all()->each(function ($user) use ($defaultCategories) {
            foreach ($defaultCategories as $categoryData) {
                Category::create([
                    'user_id' => $user->id,
                    'name' => $categoryData['name'],
                    'type' => $categoryData['type'],
                    'color' => $categoryData['color'],
                    'icon' => $categoryData['icon'],
                    'description' => $categoryData['description'],
                    'is_active' => true,
                    'sort_order' => $categoryData['sort_order']
                ]);
            }
        });
    }
}