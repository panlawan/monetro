<?php
// database/migrations/2024_01_01_000002_create_assets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name'); // ชื่อทรัพย์สิน
            $table->enum('type', [
                'property', 'vehicle', 'jewelry', 'stock', 'bond', 
                'mutual_fund', 'crypto', 'gold', 'art', 'electronics', 'other'
            ]);
            $table->decimal('purchase_price', 15, 2); // ราคาซื้อ
            $table->decimal('current_value', 15, 2); // มูลค่าปัจจุบัน
            $table->date('purchase_date'); // วันที่ซื้อ
            $table->date('valuation_date'); // วันที่ประเมินมูลค่าล่าสุด
            $table->string('location')->nullable(); // ที่ตั้ง
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // ข้อมูลเพิ่มเติม เช่น รุ่น, ปี, จำนวนหุ้น
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
};