<?php
// database/migrations/2025_08_09_000009_create_financial_goals_table.php

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
        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->decimal('monthly_contribution', 10, 2)->nullable();
            $table->date('target_date')->nullable();
            $table->enum('category', ['emergency', 'retirement', 'investment', 'purchase', 'other']);
            $table->enum('status', ['planning', 'in_progress', 'achieved', 'paused'])->default('planning');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->foreignId('linked_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status'], 'financial_goals_user_status_idx');
            $table->index(['user_id', 'category'], 'financial_goals_user_category_idx');
            $table->index(['user_id', 'priority'], 'financial_goals_user_priority_idx');
            $table->index(['target_date'], 'financial_goals_target_date_idx');
        });

        // เพิ่ม check constraints ผ่าน raw SQL
        DB::statement('ALTER TABLE financial_goals ADD CONSTRAINT financial_goals_positive_target CHECK (target_amount > 0)');
        DB::statement('ALTER TABLE financial_goals ADD CONSTRAINT financial_goals_non_negative_current CHECK (current_amount >= 0)');
        DB::statement('ALTER TABLE financial_goals ADD CONSTRAINT financial_goals_non_negative_contribution CHECK (monthly_contribution >= 0 OR monthly_contribution IS NULL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ check constraints ก่อน
        try {
            DB::statement('ALTER TABLE financial_goals DROP CONSTRAINT financial_goals_positive_target');
            DB::statement('ALTER TABLE financial_goals DROP CONSTRAINT financial_goals_non_negative_current');
            DB::statement('ALTER TABLE financial_goals DROP CONSTRAINT financial_goals_non_negative_contribution');
        } catch (Exception $e) {
            // Ignore errors
        }
        
        Schema::dropIfExists('financial_goals');
    }
};