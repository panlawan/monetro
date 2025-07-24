<?php
// 6. Investment Portfolio Table
// php artisan make:migration create_investment_portfolios_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('symbol'); // รหัสหุ้น/กองทุน
            $table->string('name'); // ชื่อหุ้น/กองทุน
            $table->enum('type', ['stock', 'fund', 'etf', 'bond', 'crypto', 'gold', 'other']);
            $table->decimal('quantity', 15, 4); // จำนวนหน่วย
            $table->decimal('average_cost', 15, 4); // ต้นทุนเฉลี่ย
            $table->decimal('current_price', 15, 4)->nullable(); // ราคาปัจจุบัน
            $table->decimal('market_value', 15, 2)->nullable(); // มูลค่าตลาด
            $table->decimal('unrealized_gain_loss', 15, 2)->nullable(); // กำไร/ขาดทุนที่ยังไม่ได้รับรู้
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->unique(['user_id', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_portfolios');
    }
};