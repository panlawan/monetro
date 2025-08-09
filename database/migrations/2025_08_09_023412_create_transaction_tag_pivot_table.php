<?php
// database/migrations/2025_08_09_000005_create_transaction_tag_pivot_table.php

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
        Schema::create('transaction_tag_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('transaction_tags')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint - transaction + tag combination
            $table->unique(['transaction_id', 'tag_id'], 'transaction_tag_unique');
            
            // Indexes
            $table->index(['transaction_id'], 'transaction_tag_transaction_idx');
            $table->index(['tag_id'], 'transaction_tag_tag_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_tag_pivot');
    }
};