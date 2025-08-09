<?php
// database/migrations/2025_08_09_000013_create_monthly_summaries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_expense', 15, 2)->default(0);
            $table->decimal('net_income', 15, 2)->default(0);
            $table->decimal('total_transfers', 15, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->integer('transfer_count')->default(0);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['user_id', 'year', 'month'], 'monthly_summaries_user_year_month_unique');
            
            // Indexes
            $table->index(['user_id', 'year'], 'monthly_summaries_user_year_idx');
            $table->index(['year', 'month'], 'monthly_summaries_year_month_idx');
        });

        // เพิ่ม check constraints ผ่าน raw SQL
        DB::statement('ALTER TABLE monthly_summaries ADD CONSTRAINT monthly_summaries_valid_year CHECK (year >= 2000 AND year <= 9999)');
        DB::statement('ALTER TABLE monthly_summaries ADD CONSTRAINT monthly_summaries_valid_month CHECK (month >= 1 AND month <= 12)');
        DB::statement('ALTER TABLE monthly_summaries ADD CONSTRAINT monthly_summaries_non_negative_transaction_count CHECK (transaction_count >= 0)');
        DB::statement('ALTER TABLE monthly_summaries ADD CONSTRAINT monthly_summaries_non_negative_transfer_count CHECK (transfer_count >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ check constraints ก่อน
        try {
            DB::statement('ALTER TABLE monthly_summaries DROP CONSTRAINT monthly_summaries_valid_year');
            DB::statement('ALTER TABLE monthly_summaries DROP CONSTRAINT monthly_summaries_valid_month');
            DB::statement('ALTER TABLE monthly_summaries DROP CONSTRAINT monthly_summaries_non_negative_transaction_count');
            DB::statement('ALTER TABLE monthly_summaries DROP CONSTRAINT monthly_summaries_non_negative_transfer_count');
        } catch (Exception $e) {
            // Ignore errors
        }
        
        Schema::dropIfExists('monthly_summaries');
    }
};