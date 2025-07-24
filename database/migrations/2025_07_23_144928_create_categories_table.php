<?php
// สร้าง Migrations สำหรับระบบการเงิน

// 1. Categories Table
// php artisan make:migration create_categories_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#6c757d'); // Hex color
            $table->enum('type', ['income', 'expense', 'investment'])->default('expense');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};