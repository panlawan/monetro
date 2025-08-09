<?php
// database/migrations/2025_08_09_000011_create_transaction_attachments_table.php

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
        Schema::create('transaction_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('original_name', 255);
            $table->integer('file_size'); // bytes
            $table->string('mime_type', 100);
            $table->string('description', 255)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['transaction_id'], 'transaction_attachments_transaction_idx');
            $table->index(['mime_type'], 'transaction_attachments_mime_idx');
        });

        // เพิ่ม check constraints ผ่าน raw SQL
        DB::statement('ALTER TABLE transaction_attachments ADD CONSTRAINT transaction_attachments_positive_size CHECK (file_size > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ check constraints ก่อน
        try {
            DB::statement('ALTER TABLE transaction_attachments DROP CONSTRAINT transaction_attachments_positive_size');
        } catch (Exception $e) {
            // Ignore errors
        }
        
        Schema::dropIfExists('transaction_attachments');
    }
};