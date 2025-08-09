<?php
// database/migrations/2025_08_09_000007_create_budget_plans_table.php

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
        Schema::create('budget_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_budget', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_active'], 'budget_plans_user_active_idx');
            $table->index(['user_id', 'start_date', 'end_date'], 'budget_plans_user_date_range_idx');
        });

        // เพิ่ม check constraints ผ่าน raw SQL
        DB::statement('ALTER TABLE budget_plans ADD CONSTRAINT budget_plans_valid_date_range CHECK (end_date > start_date)');
        DB::statement('ALTER TABLE budget_plans ADD CONSTRAINT budget_plans_positive_budget CHECK (total_budget > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ check constraints ก่อน
        try {
            DB::statement('ALTER TABLE budget_plans DROP CONSTRAINT budget_plans_valid_date_range');
            DB::statement('ALTER TABLE budget_plans DROP CONSTRAINT budget_plans_positive_budget');
        } catch (Exception $e) {
            // Ignore errors
        }
        
        Schema::dropIfExists('budget_plans');
    }
};