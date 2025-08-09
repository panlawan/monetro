<?php
// database/migrations/2025_08_09_000008_create_budget_categories_table.php

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
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('spent_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->boolean('is_flexible')->default(true);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['budget_plan_id', 'category_id'], 'budget_categories_plan_category_unique');
            
            // Indexes
            $table->index(['budget_plan_id'], 'budget_categories_plan_idx');
            $table->index(['category_id'], 'budget_categories_category_idx');
        });

        // เพิ่ม check constraints ผ่าน raw SQL
        DB::statement('ALTER TABLE budget_categories ADD CONSTRAINT budget_categories_non_negative_allocated CHECK (allocated_amount >= 0)');
        DB::statement('ALTER TABLE budget_categories ADD CONSTRAINT budget_categories_non_negative_spent CHECK (spent_amount >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ check constraints ก่อน
        try {
            DB::statement('ALTER TABLE budget_categories DROP CONSTRAINT budget_categories_non_negative_allocated');
            DB::statement('ALTER TABLE budget_categories DROP CONSTRAINT budget_categories_non_negative_spent');
        } catch (Exception $e) {
            // Ignore errors
        }
        
        Schema::dropIfExists('budget_categories');
    }
};