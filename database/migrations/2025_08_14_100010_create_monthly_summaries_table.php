<?php

// database/migrations/2025_01_01_000000_create_monthly_summaries_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year');
            $table->tinyInteger('month'); // 1..12
            $table->decimal('total_income', 14, 2)->default(0);
            $table->decimal('total_expense', 14, 2)->default(0);
            $table->decimal('net_income', 14, 2)->default(0);
            $table->decimal('total_transfers', 14, 2)->default(0);
            $table->unsignedInteger('transaction_count')->default(0);
            $table->unsignedInteger('transfer_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id','year','month']);
            $table->index(['user_id','year','month']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('monthly_summaries');
    }
};
