// database/migrations/2024_01_01_000001_create_accounts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // ชื่อบัญชี เช่น "กสิกรไทย", "PromptPay", "เงินสด"
            $table->enum('type', [
                'cash', 'savings', 'checking', 'credit_card', 
                'investment', 'crypto', 'loan', 'asset'
            ]);
            $table->string('account_number')->nullable(); // เลขบัญชี
            $table->string('bank_name')->nullable(); // ธนาคาร
            $table->decimal('balance', 15, 2)->default(0); // ยอดคงเหลือ
            $table->decimal('credit_limit', 15, 2)->nullable(); // วงเงินบัตรเครดิต
            $table->string('currency', 3)->default('THB'); // สกุลเงิน
            $table->string('color', 7)->default('#3B82F6'); // สีสำหรับแสดงผล
            $table->string('icon')->default('credit-card'); // ไอคอน
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};