<?php
// 2. Transactions Table  
// php artisan make:migration create_transactions_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense', 'investment', 'transfer']);
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->date('transaction_date');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // เพิ่มข้อมูลตาม Google Sheets
            $table->string('payment_method')->nullable(); // Cash, Bank Transfer, Credit Card
            $table->string('reference_number')->nullable(); // เลขที่อ้างอิง
            $table->text('notes')->nullable();
            $table->string('location')->nullable(); // สถานที่ทำรายการ
            $table->json('tags')->nullable(); // แท็กต่างๆ
            
            // ข้อมูลสำหรับ Investment
            $table->decimal('unit_price', 15, 4)->nullable(); // ราคาต่อหน่วย
            $table->decimal('quantity', 15, 4)->nullable(); // จำนวนหน่วย
            $table->string('symbol')->nullable(); // รหัสหุ้น/กองทุน
            
            // Status & Tracking
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamp('processed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'type', 'transaction_date']);
            $table->index(['category_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};