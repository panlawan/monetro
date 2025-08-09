<?php
// database/migrations/2025_08_09_000001_add_expense_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // เพิ่มฟิลด์สำหรับระบบการเงิน
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 50)->default('Asia/Bangkok')->after('email_verified_at');
            }
            
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency', 3)->default('THB')->after('timezone');
            }
            
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('currency');
            }
            
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }
            
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('avatar');
            }
            
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
            
            // เพิ่ม soft deletes ถ้ายังไม่มี
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
        
        // เพิ่ม indexes สำหรับประสิทธิภาพ
        Schema::table('users', function (Blueprint $table) {
            $table->index(['is_active'], 'users_is_active_idx');
            $table->index(['currency'], 'users_currency_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ลบ indexes ก่อน
            $table->dropIndex('users_is_active_idx');
            $table->dropIndex('users_currency_idx');
            
            // ลบ columns ที่เพิ่ม (เฉพาะที่เรา add เอง)
            $table->dropColumn([
                'timezone', 'currency', 'phone', 'avatar', 
                'is_active', 'last_login_at', 'deleted_at'
            ]);
        });
    }
};