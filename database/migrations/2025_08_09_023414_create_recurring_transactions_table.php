<?php
// database/migrations/2025_08_09_000010_create_recurring_transactions_table.php

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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('template_name', 255);
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->integer('interval_value')->default(1);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_due_date');
            $table->date('last_generated_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_generate')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_active'], 'recurring_transactions_user_active_idx');
            $table->index(['next_due_date', 'is_active'], 'recurring_transactions_next_due_active_idx');
            $table->index(['auto_generate', 'is_active'], 'recurring_transactions_auto_active_idx');
        });

        // เพิ่ม check constraints ผ่าน raw SQL
        DB::statement('ALTER TABLE recurring_transactions ADD CONSTRAINT recurring_transactions_positive_amount CHECK (amount > 0)');
        DB::statement('ALTER TABLE recurring_transactions ADD CONSTRAINT recurring_transactions_positive_interval CHECK (interval_value > 0)');
        DB::statement('ALTER TABLE recurring_transactions ADD CONSTRAINT recurring_transactions_valid_date_range CHECK (end_date IS NULL OR end_date > start_date)');
        DB::statement('ALTER TABLE recurring_transactions ADD CONSTRAINT recurring_transactions_valid_next_due CHECK (next_due_date >= start_date)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ check constraints ก่อน
        try {
            DB::statement('ALTER TABLE recurring_transactions DROP CONSTRAINT recurring_transactions_positive_amount');
            DB::statement('ALTER TABLE recurring_transactions DROP CONSTRAINT recurring_transactions_positive_interval');
            DB::statement('ALTER TABLE recurring_transactions DROP CONSTRAINT recurring_transactions_valid_date_range');
            DB::statement('ALTER TABLE recurring_transactions DROP CONSTRAINT recurring_transactions_valid_next_due');
        } catch (Exception $e) {
            // Ignore errors
        }
        
        Schema::dropIfExists('recurring_transactions');
    }
};