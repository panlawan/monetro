<?php
// database/migrations/2025_08_09_000006_create_transfers_table.php

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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('to_account_id')->constrained('accounts')->onDelete('cascade');
            
            // Transfer details
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->text('description')->nullable();
            $table->datetime('transfer_date');
            $table->string('reference_number', 50)->nullable();
            
            // Reference to created transactions
            $table->foreignId('from_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->foreignId('to_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'transfer_date'], 'transfers_user_date_idx');
            $table->index(['from_account_id', 'transfer_date'], 'transfers_from_account_date_idx');
            $table->index(['to_account_id', 'transfer_date'], 'transfers_to_account_date_idx');
        });

        // เพิ่ม check constraints ผ่าน raw SQL (หลังจากสร้าง table แล้ว)
        DB::statement('ALTER TABLE transfers ADD CONSTRAINT transfers_different_accounts CHECK (from_account_id != to_account_id)');
        DB::statement('ALTER TABLE transfers ADD CONSTRAINT transfers_positive_amount CHECK (amount > 0)');
        DB::statement('ALTER TABLE transfers ADD CONSTRAINT transfers_non_negative_fee CHECK (fee >= 0)');
        DB::statement('ALTER TABLE transfers ADD CONSTRAINT transfers_positive_exchange_rate CHECK (exchange_rate > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ check constraints ก่อน (ถ้ามี)
        try {
            DB::statement('ALTER TABLE transfers DROP CONSTRAINT transfers_different_accounts');
            DB::statement('ALTER TABLE transfers DROP CONSTRAINT transfers_positive_amount');
            DB::statement('ALTER TABLE transfers DROP CONSTRAINT transfers_non_negative_fee');
            DB::statement('ALTER TABLE transfers DROP CONSTRAINT transfers_positive_exchange_rate');
        } catch (Exception $e) {
            // Ignore errors if constraints don't exist
        }
        
        Schema::dropIfExists('transfers');
    }
};