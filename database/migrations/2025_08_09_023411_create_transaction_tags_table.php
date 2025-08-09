<?php
// database/migrations/2025_08_09_000004_create_transaction_tags_table.php

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
        Schema::create('transaction_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 50);
            $table->string('color', 7)->default('#6c757d');
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            // Unique constraint - user ไม่สามารถมี tag ชื่อเดียวกันได้
            $table->unique(['user_id', 'name'], 'transaction_tags_user_name_unique');
            
            // Indexes
            $table->index(['user_id', 'usage_count'], 'transaction_tags_user_usage_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_tags');
    }
};