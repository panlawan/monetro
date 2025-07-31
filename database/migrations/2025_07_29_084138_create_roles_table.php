<?php
// database/migrations/xxxx_create_roles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'admin', 'user', 'moderator'
            $table->string('display_name'); // 'Administrator', 'Regular User'
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // ['users.create', 'users.read']
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};