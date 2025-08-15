<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            // สร้างใหม่ทั้งตาราง (มาตรฐาน)
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 20);                     // 'income' | 'expense'
                $table->decimal('amount', 14, 2);               // จำนวนเงิน
                $table->date('transaction_date');               // วันที่ทำรายการ
                $table->string('note', 255)->nullable();
                $table->timestamps();
                $table->index(['user_id', 'transaction_date']);
                $table->index(['user_id', 'type', 'transaction_date']);
            });
            return;
        }

        // มีตารางแล้ว → เติมคอลัมน์ที่ขาด แบบปลอดภัย
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id');
            }
            if (!Schema::hasColumn('transactions', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('transactions', 'type')) {
                $table->string('type', 20)->default('expense')->after('category_id');
            }
            if (!Schema::hasColumn('transactions', 'amount')) {
                $table->decimal('amount', 14, 2)->default(0)->after('type');
            }
            if (!Schema::hasColumn('transactions', 'transaction_date')) {
                $table->date('transaction_date')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('transactions', 'note')) {
                $table->string('note', 255)->nullable()->after('transaction_date');
            }
            // เผื่อบางตารางเก่ายังไม่มี timestamps
            if (!Schema::hasColumn('transactions', 'created_at') || !Schema::hasColumn('transactions', 'updated_at')) {
                $table->timestamps();
            }
        });

        // ถ้ามีคอลัมน์เก่าชื่ออื่น ๆ → map ค่ามายังคอลัมน์มาตรฐาน
        if (Schema::hasColumn('transactions', 'transaction_amount')) {
            DB::statement('UPDATE transactions SET amount = transaction_amount WHERE amount = 0 AND transaction_amount IS NOT NULL');
        }
        if (Schema::hasColumn('transactions', 'date')) {
            DB::statement('UPDATE transactions SET transaction_date = `date` WHERE transaction_date IS NULL AND `date` IS NOT NULL');
        }
        if (Schema::hasColumn('transactions', 'transaction_type')) {
            DB::statement('UPDATE transactions SET `type` = transaction_type WHERE transaction_type IS NOT NULL');
        }

        // เพิ่มดัชนี ถ้ายังไม่มี (กัน duplicate ชื่อ index)
        $hasIndex = fn (string $name) =>
            collect(DB::select("SHOW INDEX FROM transactions WHERE Key_name = ?", [$name]))->isNotEmpty();

        if (!$hasIndex('transactions_user_id_transaction_date_index')) {
            Schema::table('transactions', fn (Blueprint $t) => $t->index(['user_id','transaction_date']));
        }
        if (!$hasIndex('transactions_user_id_type_transaction_date_index')) {
            Schema::table('transactions', fn (Blueprint $t) => $t->index(['user_id','type','transaction_date']));
        }
    }

    public function down(): void
    {
        // ไม่ย้อนเพื่อความปลอดภัยของข้อมูล
    }
};
