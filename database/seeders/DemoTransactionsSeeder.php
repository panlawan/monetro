<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;

class DemoTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        // เลือก user เป้าหมาย
        $seedUserId = env('SEED_USER_ID'); // ตั้งค่าได้ชั่วคราวตอนสั่ง seed
        $user = $seedUserId
            ? User::find($seedUserId)
            : User::query()->orderBy('id')->first();

        if (!$user) {
            $this->command->warn('No user found. Skipping transactions seeding.');
            return;
        }

        $now = now();
        $rows = [];

        // สุ่ม 10 แถวภายใน 90 วันที่ผ่านมา (ครึ่ง income / ครึ่ง expense โดยประมาณ)
        for ($i = 0; $i < 10; $i++) {
            $isIncome = (bool)random_int(0, 1);
            $amount   = $isIncome
                ? random_int(2000, 15000) / 1.0   // รายรับ 2,000–15,000
                : random_int(100, 5000) / 1.0;    // รายจ่าย 100–5,000

            $rows[] = [
                'user_id'          => $user->id,
                'category_id'      => null,                 // ถ้ามี categories อยู่แล้ว ค่อยมา map ทีหลัง
                'type'             => $isIncome ? 'income' : 'expense',
                'amount'           => $amount,
                'transaction_date' => Carbon::now()->subDays(random_int(0, 90))->toDateString(),
                'note'             => $isIncome ? 'Demo income' : 'Demo expense',
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        DB::table('transactions')->insert($rows);

        $this->command->info("Seeded 10 demo transactions for user #{$user->id} ({$user->email}).");
    }
}
