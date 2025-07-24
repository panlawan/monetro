<?php
// 3. Budgets Table
// php artisan make:migration create_budgets_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 15, 2); // งบประมาณ
            $table->decimal('spent', 15, 2)->default(0); // ใช้ไปแล้ว
            $table->enum('period', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Alert settings
            $table->boolean('alert_enabled')->default(true);
            $table->integer('alert_percentage')->default(80); // แจ้งเตือนเมื่อใช้ % นี้
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'period', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};