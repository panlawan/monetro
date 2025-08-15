<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** ตรวจว่ามี index ชื่อ $name ในตาราง $table หรือไม่ (MySQL) */
    private function hasIndex(string $table, string $name): bool
    {
        try {
            return collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$name]))->isNotEmpty();
        } catch (\Throwable $e) {
            // ถ้าไม่ใช่ MySQL ก็ถือว่าไม่พบ เพื่อป้องกัน error
            return false;
        }
    }

    public function up(): void
    {
        // กรณีไม่มีตาราง -> สร้างใหม่ตามมาตรฐาน
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 20);           // 'income' | 'expense'
                $table->decimal('amount', 14, 2);     // จำนวนเงิน
                $table->date('transaction_date');     // วันที่ทำรายการ
                $table->string('note', 255)->nullable();
                $table->timestamps();

                $table->index(['user_id', 'transaction_date'], 'transactions_user_id_transaction_date_index');
                $table->index(['user_id', 'type', 'transaction_date'], 'transactions_user_id_type_transaction_date_index');
            });

            return;
        }

        // มีตารางอยู่แล้ว -> เติมคอลัมน์ที่จำเป็น (ไม่ใช้ AFTER ป้องกันล้ม)
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'type')) {
                $table->string('type', 20)->nullable();
            }
            if (!Schema::hasColumn('transactions', 'amount')) {
                $table->decimal('amount', 14, 2)->nullable();
            }
            if (!Schema::hasColumn('transactions', 'transaction_date')) {
                $table->date('transaction_date')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'note')) {
                $table->string('note', 255)->nullable();
            }
            if (!Schema::hasColumn('transactions', 'created_at') && !Schema::hasColumn('transactions', 'updated_at')) {
                $table->timestamps();
            }
        });

        // Backfill: ถ้ามีคอลัมน์เก่า ใช้ค่าเดิมมาใส่คอลัมน์มาตรฐาน
        if (Schema::hasColumn('transactions', 'transaction_amount')) {
            DB::statement('UPDATE transactions SET amount = transaction_amount WHERE amount IS NULL AND transaction_amount IS NOT NULL');
        }
        if (Schema::hasColumn('transactions', 'date')) {
            DB::statement('UPDATE transactions SET transaction_date = `date` WHERE transaction_date IS NULL AND `date` IS NOT NULL');
        }
        if (Schema::hasColumn('transactions', 'transaction_type')) {
            DB::statement("UPDATE transactions SET `type` = transaction_type WHERE `type` IS NULL AND transaction_type IS NOT NULL");
        }

        // ดัชนีที่ใช้โดย ReportService
        if (!$this->hasIndex('transactions', 'transactions_user_id_transaction_date_index')) {
            Schema::table('transactions', fn (Blueprint $t) => $t->index(['user_id', 'transaction_date'], 'transactions_user_id_transaction_date_index'));
        }
        if (!$this->hasIndex('transactions', 'transactions_user_id_type_transaction_date_index')) {
            Schema::table('transactions', fn (Blueprint $t) => $t->index(['user_id', 'type', 'transaction_date'], 'transactions_user_id_type_transaction_date_index'));
        }

        // หมายเหตุ: ไม่บังคับ Foreign Key ที่เติมใหม่ทันที เพื่อเลี่ยงชนกับข้อมูลเดิม
        // หากต้องการ FK จริง ให้ทำ migration แยกภายหลัง หลังจาก data สะอาดแล้ว
    }

    public function down(): void
    {
        // เพื่อความปลอดภัยของข้อมูล ไม่ย้อน schema
        // (ถ้าจำเป็นจริง ๆ ค่อยทำ migration ถอดออกแบบเฉพาะกิจ)
    }
};
