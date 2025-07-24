<?php
// database/migrations/2024_01_01_000003_update_transactions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ตรวจสอบว่า table transactions มีอยู่แล้วหรือไม่
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
                $table->enum('type', ['income', 'expense'])->default('expense');
                $table->decimal('amount', 10, 2);
                $table->text('description')->nullable();
                $table->date('transaction_date');
                $table->timestamps();
            });
        }

        Schema::table('transactions', function (Blueprint $table) {
            // เพิ่มฟิลด์ใหม่ถ้ายังไม่มี
            if (!Schema::hasColumn('transactions', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('transactions', 'to_account_id')) {
                $table->foreignId('to_account_id')->nullable()->after('account_id')->constrained('accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('transactions', 'asset_id')) {
                $table->foreignId('asset_id')->nullable()->after('to_account_id')->constrained()->onDelete('set null');
            }
            
            // ปรับปรุงประเภทธุรกรรม
            if (Schema::hasColumn('transactions', 'type')) {
                $table->dropColumn('type');
            }
            
            if (!Schema::hasColumn('transactions', 'transaction_type')) {
                $table->enum('transaction_type', [
                    'income', 'expense', 'transfer', 'investment_buy', 'investment_sell',
                    'dividend', 'interest', 'capital_gain', 'capital_loss', 'asset_purchase',
                    'asset_sale', 'loan_payment', 'loan_receive'
                ])->after('category_id')->default('expense');
            }
            
            if (!Schema::hasColumn('transactions', 'quantity')) {
                $table->decimal('quantity', 10, 4)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('transactions', 'price_per_unit')) {
                $table->decimal('price_per_unit', 15, 4)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('transactions', 'symbol')) {
                $table->string('symbol')->nullable()->after('price_per_unit');
            }
            if (!Schema::hasColumn('transactions', 'metadata')) {
                $table->json('metadata')->nullable()->after('description');
            }
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['to_account_id']);
            $table->dropForeign(['asset_id']);
            $table->dropColumn([
                'account_id', 'to_account_id', 'asset_id', 'quantity', 
                'price_per_unit', 'symbol', 'metadata', 'transaction_type'
            ]);
            $table->enum('type', ['income', 'expense'])->default('expense');
        });
    }
};