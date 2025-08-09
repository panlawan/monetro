<?php
// database/migrations/2025_08_09_000003_create_transactions_table.php

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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            
            // Transaction details
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->datetime('transaction_date');
            $table->string('reference_number', 50)->nullable();
            $table->string('location', 255)->nullable();
            $table->text('notes')->nullable();
            
            // Recurring transaction fields
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_type', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->foreignId('parent_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['user_id', 'transaction_date'], 'transactions_user_date_idx');
            $table->index(['account_id', 'transaction_date'], 'transactions_account_date_idx');
            $table->index(['category_id', 'transaction_date'], 'transactions_category_date_idx');
            $table->index(['user_id', 'type', 'transaction_date'], 'transactions_user_type_date_idx');
            $table->index(['is_recurring'], 'transactions_recurring_idx');
            $table->index(['parent_transaction_id'], 'transactions_parent_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};