<?php
// app/Console/Commands/SetupFinanceData.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Goal;
use Carbon\Carbon;

class SetupFinanceData extends Command
{
    protected $signature = 'finance:setup {--demo : Create demo data} {--user= : Setup for specific user ID}';
    protected $description = 'Setup finance data and categories for all users';

    public function handle()
    {
        $this->info('🚀 Setting up finance data...');

        // Create categories for users
        $this->createCategories();

        if ($this->option('demo')) {
            $this->info('📊 Creating demo data...');
            $this->createDemoData();
        }

        $this->info('✅ Finance setup completed successfully!');
        return 0;
    }

    private function createCategories()
    {
        $this->info('📝 Creating default categories...');

        $defaultCategories = [
            // Income Categories
            [
                'name' => 'เงินเดือน',
                'type' => 'income',
                'color' => '#1cc88a',
                'icon' => 'fa-money-bill-wave',
                'description' => 'รายได้จากการทำงานประจำ',
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
                'description' => 'ผลตอบแทนจากการลงทุน',
                'sort_order' => 3
            ],
            [
                'name' => 'งานพิเศษ',
                'type' => 'income',
                'color' => '#e74a3b',
                'icon' => 'fa-briefcase',
                'description' => 'รายได้จากงานพิเศษ',
                'sort_order' => 4
            ],
            [
                'name' => 'รายได้อื่นๆ',
                'type' => 'income',
                'color' => '#858796',
                'icon' => 'fa-coins',
                'description' => 'รายได้อื่นๆ ที่ไม่ได้จัดหมวดหมู่',
                'sort_order' => 5
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
                'description' => 'ค่าเดินทางและยานพาหนะ',
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
                'description' => 'ค่ารักษาพยาบาล ยา วิตามิน',
                'sort_order' => 4
            ],
            [
                'name' => 'การศึกษา',
                'type' => 'expense',
                'color' => '#3498db',
                'icon' => 'fa-graduation-cap',
                'description' => 'ค่าเรียน หนังสือ คอร์ส',
                'sort_order' => 5
            ],
            [
                'name' => 'ช้อปปิ้ง',
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
                'description' => 'ภาพยนตร์ เกม กิจกรรมพักผ่อน',
                'sort_order' => 7
            ],
            [
                'name' => 'การออม',
                'type' => 'expense',
                'color' => '#1abc9c',
                'icon' => 'fa-piggy-bank',
                'description' => 'เงินออม การลงทุน',
                'sort_order' => 8
            ],
            [
                'name' => 'ประกันภัย',
                'type' => 'expense',
                'color' => '#34495e',
                'icon' => 'fa-shield-alt',
                'description' => 'ประกันชีวิต ประกันสุขภาพ',
                'sort_order' => 9
            ],
            [
                'name' => 'อื่นๆ',
                'type' => 'expense',
                'color' => '#95a5a6',
                'icon' => 'fa-ellipsis-h',
                'description' => 'รายจ่ายอื่นๆ ที่ไม่ได้จัดหมวดหมู่',
                'sort_order' => 10
            ]
        ];

        // Get users to create categories for
        $userId = $this->option('user');
        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("User with ID {$userId} not found!");
                return;
            }
        } else {
            $users = User::all();
        }

        if ($users->isEmpty()) {
            $this->error('No users found! Please create users first.');
            return;
        }

        $categoryCount = 0;
        $userCount = 0;

        foreach ($users as $user) {
            $this->info("   👤 Creating categories for user: {$user->name} (ID: {$user->id})");
            
            foreach ($defaultCategories as $categoryData) {
                $category = Category::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $categoryData['name'],
                        'type' => $categoryData['type']
                    ],
                    [
                        'color' => $categoryData['color'],
                        'icon' => $categoryData['icon'],
                        'description' => $categoryData['description'],
                        'is_active' => true,
                        'sort_order' => $categoryData['sort_order']
                    ]
                );

                if ($category->wasRecentlyCreated) {
                    $categoryCount++;
                }
            }
            $userCount++;
        }

        $this->info("   ✅ Created {$categoryCount} categories for {$userCount} users");
    }

    private function createDemoData()
    {
        // Get users
        $userId = $this->option('user');
        if ($userId) {
            $users = User::where('id', $userId)->get();
        } else {
            $users = User::all();
        }

        $transactionCount = 0;
        $goalCount = 0;

        foreach ($users as $user) {
            $this->info("   👤 Creating demo data for user: {$user->name}");
            
            // Create sample transactions
            $userTransactions = $this->createSampleTransactions($user);
            $transactionCount += $userTransactions;
            
            // Create sample goals
            $userGoals = $this->createSampleGoals($user);
            $goalCount += $userGoals;
        }

        $this->info("   ✅ Created {$transactionCount} transactions and {$goalCount} goals");
    }

    private function createSampleTransactions($user)
    {
        $incomeCategories = Category::where('user_id', $user->id)
            ->where('type', 'income')
            ->get();
            
        $expenseCategories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->get();

        if ($incomeCategories->isEmpty() || $expenseCategories->isEmpty()) {
            $this->warn("      ⚠️ No categories found for user {$user->name}. Skipping transactions.");
            return 0;
        }

        $transactionCount = 0;

        // Create transactions for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            
            // Monthly salary (consistent income)
            $salaryCategory = $incomeCategories->where('name', 'เงินเดือน')->first() ?? $incomeCategories->first();
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $salaryCategory->id,
                'amount' => rand(35000, 55000),
                'description' => 'เงินเดือนประจำเดือน ' . $month->format('F Y'),
                'notes' => 'รายได้ประจำเดือน',
                'transaction_date' => $month->copy()->day(25),
                'type' => 'income'
            ]);
            $transactionCount++;

            // Optional: Business income
            if (rand(1, 3) === 1) { // 33% chance
                $businessCategory = $incomeCategories->where('name', 'ธุรกิจส่วนตัว')->first();
                if ($businessCategory) {
                    Transaction::create([
                        'user_id' => $user->id,
                        'category_id' => $businessCategory->id,
                        'amount' => rand(5000, 25000),
                        'description' => 'รายได้จากธุรกิจส่วนตัว',
                        'notes' => 'รายได้เสริมจากงานพิเศษ',
                        'transaction_date' => $month->copy()->day(rand(1, 28)),
                        'type' => 'income'
                    ]);
                    $transactionCount++;
                }
            }

            // Generate random expenses for the month
            $monthlyExpenses = rand(15, 25); // 15-25 expenses per month
            for ($j = 0; $j < $monthlyExpenses; $j++) {
                $category = $expenseCategories->random();
                $amount = $this->getRandomAmount($category->name);
                $description = $this->getRandomDescription($category->name);
                
                Transaction::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'amount' => $amount,
                    'description' => $description,
                    'notes' => $this->getRandomNotes($category->name),
                    'transaction_date' => $month->copy()->day(rand(1, 28)),
                    'type' => 'expense'
                ]);
                $transactionCount++;
            }
        }

        return $transactionCount;
    }

    private function createSampleGoals($user)
    {
        $sampleGoals = [
            [
                'name' => 'Emergency Fund',
                'description' => 'เงินฉุกเฉินสำหรับค่าใช้จ่าย 6 เดือน',
                'target_amount' => 200000,
                'current_amount' => rand(50000, 120000),
                'target_date' => now()->addMonths(rand(6, 12)),
                'color' => '#1cc88a',
                'icon' => 'fa-shield-alt'
            ],
            [
                'name' => 'ซื้อรถยนต์',
                'description' => 'เก็บเงินซื้อรถยนต์คันใหม่',
                'target_amount' => 600000,
                'current_amount' => rand(100000, 250000),
                'target_date' => now()->addMonths(rand(12, 24)),
                'color' => '#36b9cc',
                'icon' => 'fa-car'
            ],
            [
                'name' => 'ท่องเที่ยวญี่ปุ่น',
                'description' => 'เก็บเงินไปเที่ยวญี่ปุ่น 10 วัน',
                'target_amount' => 100000,
                'current_amount' => rand(20000, 60000),
                'target_date' => now()->addMonths(rand(4, 8)),
                'color' => '#f6c23e',
                'icon' => 'fa-plane'
            ],
            [
                'name' => 'ซื้อบ้าน',
                'description' => 'เงินดาวน์สำหรับซื้อบ้าน',
                'target_amount' => 800000,
                'current_amount' => rand(150000, 400000),
                'target_date' => now()->addMonths(rand(18, 36)),
                'color' => '#e74a3b',
                'icon' => 'fa-home'
            ],
            [
                'name' => 'เกษียณก่อนวัย',
                'description' => 'เงินสำหรับเกษียณก่อนวัย 50',
                'target_amount' => 5000000,
                'current_amount' => rand(500000, 1500000),
                'target_date' => now()->addYears(rand(10, 20)),
                'color' => '#858796',
                'icon' => 'fa-calendar-check'
            ]
        ];

        $goalCount = 0;
        $numberOfGoals = rand(2, 4); // Create 2-4 goals per user

        for ($i = 0; $i < $numberOfGoals; $i++) {
            if (isset($sampleGoals[$i])) {
                $goalData = $sampleGoals[$i];
                
                Goal::create([
                    'user_id' => $user->id,
                    'name' => $goalData['name'],
                    'description' => $goalData['description'],
                    'target_amount' => $goalData['target_amount'],
                    'current_amount' => $goalData['current_amount'],
                    'target_date' => $goalData['target_date'],
                    'status' => 'active',
                    'color' => $goalData['color'],
                    'icon' => $goalData['icon']
                ]);
                $goalCount++;
            }
        }

        return $goalCount;
    }

    private function getRandomAmount($categoryName)
    {
        $amounts = [
            'อาหาร' => [50, 500],
            'คมนาคม' => [30, 300],
            'ที่พัก' => [8000, 20000],
            'สุขภาพ' => [200, 3000],
            'การศึกษา' => [500, 5000],
            'ช้อปปิ้ง' => [200, 8000],
            'บันเทิง' => [100, 2000],
            'การออม' => [2000, 15000],
            'ประกันภัย' => [1000, 8000],
            'อื่นๆ' => [100, 2000]
        ];

        $range = $amounts[$categoryName] ?? [100, 1000];
        return rand($range[0], $range[1]);
    }

    private function getRandomDescription($categoryName)
    {
        $descriptions = [
            'อาหาร' => [
                'ข้าวเที่ยงที่ออฟฟิศ',
                'ข้าวเย็นกับครอบครัว',
                'กาแฟสดตอนเช้า',
                'ขนมขบเคี้ยว',
                'ผลไม้สด',
                'อาหารเช้าก่อนทำงาน',
                'อาหารส่งถึงบ้าน',
                'ไปกินข้าวกับเพื่อน',
                'ซื้อของกินเก็บไว้ที่บ้าน',
                'กาแฟร้านโปรด'
            ],
            'คมนาคม' => [
                'ค่าน้ำมันรถยนต์',
                'ค่าโดยสาร BTS',
                'ค่าโดยสาร MRT',
                'ค่าแท็กซี่กลับบ้าน',
                'ค่าจอดรถที่ห้าง',
                'ค่าโดยสารรถเมล์',
                'ค่าบริการ Grab',
                'ค่าซ่อมรถยนต์',
                'ค่าประกันรถยนต์',
                'ค่าธรรมเนียมต่อทะเบียนรถ'
            ],
            'ที่พัก' => [
                'ค่าเช่าบ้านประจำเดือน',
                'ค่าไฟฟ้าประจำเดือน',
                'ค่าน้ำประปาประจำเดือน',
                'ค่าอินเทอร์เน็ตบ้าน',
                'ค่าทำความสะอาดบ้าน',
                'ค่าซ่อมแซมอุปกรณ์ในบ้าน',
                'ค่าส่วนกลางคอนโด',
                'ค่าขยะ',
                'ค่าดูแลสวน',
                'ค่าปรับปรุงบ้าน'
            ],
            'สุขภาพ' => [
                'ตรวจสุขภาพประจำปี',
                'ซื้อยาจากร้านขายยา',
                'ค่าหมอฟัน',
                'ซื้อวิตามินและอาหารเสริม',
                'ค่ายิมออกกำลังกาย',
                'นวดแผนไทย',
                'ตรวจสายตา',
                'ซื้ออุปกรณ์ออกกำลังกาย',
                'ค่าฝังเข็ม',
                'ค่ารักษาจากหมอเฉพาะทาง'
            ],
            'การศึกษา' => [
                'ซื้อหนังสือเพิ่มความรู้',
                'ลงทะเบียนคอร์สออนไลน์',
                'ค่าเรียนภาษาอังกฤษ',
                'ซื้อเครื่องเขียนและอุปกรณ์',
                'ค่าสัมมนาและเวิร์คชอป',
                'ซื้อซอฟต์แวร์เพื่อการเรียนรู้',
                'ค่าสมัครสมาชิกเว็บไซต์การเรียน',
                'ค่าใช้จ่ายในการเรียนต่อ',
                'ซื้ออุปกรณ์เทคโนโลยีเพื่อการเรียน',
                'ค่าเดินทางไปอบรม'
            ],
            'ช้อปปิ้ง' => [
                'ซื้อเสื้อผ้าใหม่',
                'ซื้อรองเท้าคู่ใหม่',
                'ซื้อของใช้ในบ้าน',
                'อุปกรณ์อิเล็กทรอนิกส์',
                'เครื่องสำอางและของใช้ส่วนตัว',
                'ซื้อของขวัญให้คนรัก',
                'เครื่องประดับ',
                'กระเป๋าและอุปกรณ์เดินทาง',
                'อุปกรณ์กีฬา',
                'ของตกแต่งบ้าน'
            ],
            'บันเทิง' => [
                'ดูหนังที่โรงภาพยนตร์',
                'เล่นเกมและซื้อเกม',
                'ค่าสมัคร Netflix และ streaming',
                'ไปคอนเสิร์ตและงานแสดง',
                'ไปงานเลี้ยงกับเพื่อน',
                'เล่นโบว์ลิ่งและกิจกรรม',
                'ซื้อหนังสือนิยาย',
                'ไปสวนสนุกและสถานที่ท่องเที่ยว',
                'เล่นคาราโอเกะ',
                'ซื้อของสะสม'
            ],
            'การออม' => [
                'โอนเข้าบัญชีออมทรัพย์',
                'ซื้อกองทุนรวม',
                'ลงทุนในหุ้น',
                'ฝากเงินประจำ',
                'ซื้อพันธบัตรรัฐบาล',
                'ลงทุนในทอง',
                'โอนเข้ากองทุนสำรองเลี้ยงชีพ',
                'ซื้อประกันแบบออม',
                'ลงทุนใน cryptocurrency',
                'ซื้อหน่วยลงทุน'
            ],
            'ประกันภัย' => [
                'เบี้ยประกันชีวิตประจำเดือน',
                'เบี้ยประกันสุขภาพ',
                'ประกันการเดินทาง',
                'ประกันอุบัติเหตุส่วนบุคคล',
                'ประกันโรคร้ายแรง',
                'ประกันบ้านและทรัพย์สิน',
                'ประกันรถยนต์ประจำปี',
                'ประกันการศึกษาบุตร',
                'ประกันชีวิตแบบสะสมทรัพย์',
                'ประกันสำหรับผู้สูงอายุ'
            ],
            'อื่นๆ' => [
                'ค่าธรรมเนียมธนาคาร',
                'ซื้อของขวัญวันเกิดเพื่อน',
                'บริจาคเพื่อการกุศล',
                'ค่าซ่อมแซมของใช้',
                'ค่าบริการต่างๆ',
                'ค่าส่งพัสดุ',
                'ซื้อลอตเตอรี่',
                'ค่าทำบุญที่วัด',
                'ค่าใช้จ่ายในงานสังคม',
                'รายจ่ายไม่สามารถระบุได้'
            ]
        ];

        $categoryDescriptions = $descriptions[$categoryName] ?? ['รายจ่ายทั่วไป'];
        return $categoryDescriptions[array_rand($categoryDescriptions)];
    }

    private function getRandomNotes($categoryName)
    {
        $notes = [
            'อาหาร' => [
                'อร่อยมาก ต้องกลับมาอีก',
                'อาหารคุณภาพดี ราคาเหมาะสม',
                'สั่งผ่านแอปส่งอาหาร',
                'ไปกินกับเพื่อนร่วมงาน',
                'ลองร้านใหม่ที่เปิดใกล้บ้าน',
                null, null, null // Some transactions won't have notes
            ],
            'คมนาคม' => [
                'ฝนตก เลยต้องเรียกแท็กซี่',
                'รถเสีย ต้องใช้ขนส่งสาธารณะ',
                'ไปทำงานนอกสถานที่',
                'เดินทางไปพบลูกค้า',
                null, null, null
            ],
            'ที่พัก' => [
                'ค่าใช้จ่ายประจำเดือน',
                'มีการปรับขึ้นจากเดือนที่แล้ว',
                'รวมค่าส่วนกลางแล้ว',
                null, null
            ],
            'สุขภาพ' => [
                'การดูแลสุขภาพเป็นสิ่งสำคัญ',
                'ป้องกันไว้ก่อนดีกว่าแก้',
                'ตามคำแนะนำของหมอ',
                null, null, null
            ],
            'การศึกษา' => [
                'การลงทุนในตัวเองดีที่สุด',
                'เพิ่มทักษะเพื่อความก้าวหน้า',
                'อยากเรียนรู้สิ่งใหม่ๆ',
                null, null
            ],
            'ช้อปปิ้ง' => [
                'ลดราคาจากเดิม 50%',
                'ซื้อของจำเป็นเท่านั้น',
                'ได้โปรโมชั่นพิเศษ',
                'ของเก่าเสียหาย ต้องซื้อใหม่',
                null, null
            ],
            'บันเทิง' => [
                'ผ่อนคลายหลังจากทำงานหนัก',
                'ใช้เวลากับครอบครัวและเพื่อน',
                'สมควรให้รางวัลตัวเองบ้าง',
                null, null, null
            ],
            'การออม' => [
                'วินัยในการออมเงินประจำเดือน',
                'เก็บเงินสำหรับอนาคต',
                'ลงทุนเพื่อความมั่นคงทางการเงิน',
                null, null
            ],
            'ประกันภัย' => [
                'ความปลอดภัยทางการเงิน',
                'ป้องกันความเสี่ยงในอนาคต',
                null, null, null
            ],
            'อื่นๆ' => [
                'รายจ่ายที่ไม่ได้คาดคิด',
                'ค่าใช้จ่ายเบ็ดเตล็ด',
                null, null, null, null
            ]
        ];

        $categoryNotes = $notes[$categoryName] ?? [null, null, null];
        return $categoryNotes[array_rand($categoryNotes)];
    }
}