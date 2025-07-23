<?php
// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $incomeCategories = [
            ['name' => 'เงินเดือน', 'icon' => 'fa-money-bill-wave', 'color' => '#1cc88a'],
            ['name' => 'โบนัส', 'icon' => 'fa-gift', 'color' => '#36b9cc'],
            ['name' => 'รายได้เสริม', 'icon' => 'fa-hand-holding-usd', 'color' => '#f6c23e'],
            ['name' => 'ดอกเบี้ย', 'icon' => 'fa-percentage', 'color' => '#e74a3b'],
            ['name' => 'การลงทุน', 'icon' => 'fa-chart-line', 'color' => '#5a5c69'],
            ['name' => 'อื่นๆ', 'icon' => 'fa-plus-circle', 'color' => '#858796'],
        ];

        $expenseCategories = [
            ['name' => 'อาหารและเครื่องดื่ม', 'icon' => 'fa-utensils', 'color' => '#e74a3b'],
            ['name' => 'ที่อยู่อาศัย', 'icon' => 'fa-home', 'color' => '#f6c23e'],
            ['name' => 'การเดินทาง', 'icon' => 'fa-car', 'color' => '#36b9cc'],
            ['name' => 'สุขภาพ', 'icon' => 'fa-heartbeat', 'color' => '#1cc88a'],
            ['name' => 'การศึกษา', 'icon' => 'fa-graduation-cap', 'color' => '#667eea'],
            ['name' => 'ช้อปปิ้ง', 'icon' => 'fa-shopping-bag', 'color' => '#764ba2'],
            ['name' => 'ความบันเทิง', 'icon' => 'fa-gamepad', 'color' => '#ff6b6b'],
            ['name' => 'ค่าบริการ', 'icon' => 'fa-credit-card', 'color' => '#feca57'],
            ['name' => 'ประกันภัย', 'icon' => 'fa-shield-alt', 'color' => '#5f27cd'],
            ['name' => 'เสื้อผ้า', 'icon' => 'fa-tshirt', 'color' => '#00d2d3'],
            ['name' => 'ของใช้ส่วนตัว', 'icon' => 'fa-user', 'color' => '#ff9ff3'],
            ['name' => 'อื่นๆ', 'icon' => 'fa-ellipsis-h', 'color' => '#858796'],
        ];

        foreach ($users as $user) {
            // Create income categories
            foreach ($incomeCategories as $index => $category) {
                Category::create([
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'type' => 'income',
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }

            // Create expense categories
            foreach ($expenseCategories as $index => $category) {
                Category::create([
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'type' => 'expense',
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ Default categories created for all users!');
    }
}