<?php
// 5. Accounts Table (บัญชีธนาคาร, กระเป๋าเงิน)
// php artisan make:migration create_accounts_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ชื่อบัญชี
            $table->enum('type', ['cash', 'savings', 'checking', 'credit_card', 'investment', 'loan']);
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->nullable(); // วงเงินสำหรับบัตรเครดิต
            $table->string('currency', 3)->default('THB');
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
