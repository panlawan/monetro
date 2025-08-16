<?php
// database/seeders/TransactionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $userId = 1; // เปลี่ยนเป็น ID ของ user ที่ต้องการ
        
        // ลบข้อมูลเก่า
        DB::table('transactions')->where('user_id', $userId)->delete();
        
        $transactions = [];
        
        // สร้างข้อมูล 12 เดือนย้อนหลัง
        for ($month = 11; $month >= 0; $month--) {
            $date = Carbon::now()->subMonths($month);
            
            // Income transactions
            for ($i = 0; $i < rand(3, 8); $i++) {
                $transactions[] = [
                    'user_id' => $userId,
                    'category_id' => null,
                    'type' => 'income',
                    'amount' => rand(15000, 35000),
                    'transaction_date' => $date->copy()->addDays(rand(1, 28))->format('Y-m-d'),
                    'note' => 'Sample Income ' . ($i + 1) . ' for ' . $date->format('M Y'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Expense transactions
            for ($i = 0; $i < rand(8, 15); $i++) {
                $transactions[] = [
                    'user_id' => $userId,
                    'category_id' => null,
                    'type' => 'expense',
                    'amount' => rand(800, 12000),
                    'transaction_date' => $date->copy()->addDays(rand(1, 28))->format('Y-m-d'),
                    'note' => 'Sample Expense ' . ($i + 1) . ' - ' . ['Food', 'Transport', 'Shopping', 'Bills', 'Entertainment'][rand(0, 4)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        DB::table('transactions')->insert($transactions);
        
        $this->command->info('Sample transactions created successfully!');
        $this->command->info('Total transactions: ' . count($transactions));
        $this->command->info('For user ID: ' . $userId);
    }
}