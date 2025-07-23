<?php
// database/migrations/xxxx_create_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->enum('type', ['income', 'expense']);
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_type', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->integer('recurring_interval')->default(1);
            $table->date('recurring_end_date')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'type']);
            $table->index(['category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};