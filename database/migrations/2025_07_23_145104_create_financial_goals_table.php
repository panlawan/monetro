<?php
// 4. Financial Goals Table
// php artisan make:migration create_financial_goals_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->date('target_date');
            $table->enum('type', ['savings', 'investment', 'debt_payoff', 'purchase'])->default('savings');
            
            // Progress tracking
            $table->decimal('monthly_contribution', 15, 2)->nullable();
            $table->boolean('auto_calculate')->default(true);
            
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'completed', 'paused', 'cancelled'])->default('active');
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_goals');
    }
};