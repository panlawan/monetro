<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_admin_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role and status fields
            $table->enum('role', ['super_admin', 'admin', 'moderator', 'user'])
                  ->default('user')
                  ->after('email_verified_at');
                  
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])
                  ->default('active')
                  ->after('role');

            // Profile fields
            $table->string('avatar')->nullable()->after('phone');
            $table->string('timezone')->nullable()->after('avatar');

            // Login tracking
            $table->timestamp('last_login_at')->nullable()->after('privacy_accepted_at');
            $table->unsignedInteger('login_count')->default(0)->after('last_login_at');

            // Soft deletes
            $table->softDeletes()->after('updated_at');

            // Indexes for better performance
            $table->index(['role', 'status']);
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'role',
                'status', 
                'avatar',
                'timezone',
                'last_login_at',
                'login_count'
            ]);
            $table->dropIndex(['role', 'status']);
            $table->dropIndex(['last_login_at']);
        });
    }
};