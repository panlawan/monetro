<?php
// database/migrations/2025_08_09_000012_create_user_preferences_table.php

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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('default_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->string('default_currency', 3)->default('THB');
            $table->string('date_format', 20)->default('d/m/Y');
            $table->string('number_format', 20)->default('comma_dot'); // 1,234.56
            $table->enum('start_of_week', ['sunday', 'monday'])->default('sunday');
            $table->integer('fiscal_year_start')->default(1); // เดือนที่เริ่มปีงบประมาณ
            $table->json('dashboard_widgets')->nullable(); // การจัดเรียง widget
            $table->json('notification_settings')->nullable();
            $table->enum('theme_preference', ['light', 'dark', 'auto'])->default('light');
            $table->timestamps();
            
            // Indexes
            $table->index(['default_currency'], 'user_preferences_currency_idx');
            $table->index(['theme_preference'], 'user_preferences_theme_idx');
        });

        // เพิ่ม check constraints ผ่าน raw SQL
        DB::statement('ALTER TABLE user_preferences ADD CONSTRAINT user_preferences_valid_fiscal_month CHECK (fiscal_year_start BETWEEN 1 AND 12)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ check constraints ก่อน
        try {
            DB::statement('ALTER TABLE user_preferences DROP CONSTRAINT user_preferences_valid_fiscal_month');
        } catch (Exception $e) {
            // Ignore errors
        }
        
        Schema::dropIfExists('user_preferences');
    }
};