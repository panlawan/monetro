<?php
// database/migrations/2024_01_01_000004_create_net_worth_snapshots_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('net_worth_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('snapshot_date');
            $table->decimal('total_assets', 15, 2)->default(0);
            $table->decimal('total_liabilities', 15, 2)->default(0);
            $table->decimal('net_worth', 15, 2)->default(0);
            $table->json('breakdown')->nullable(); // รายละเอียดตามประเภทบัญชี
            $table->timestamps();
            
            $table->unique(['user_id', 'snapshot_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('net_worth_snapshots');
    }
};