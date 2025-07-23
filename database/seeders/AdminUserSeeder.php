<?php
// database/seeders/AdminUserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@monetro.io'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@monetro.io',
                'password' => Hash::make('SuperAdmin123!'),
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'timezone' => 'Asia/Bangkok',
            ]
        );

        // Create Admin
        User::firstOrCreate(
            ['email' => 'admin@monetro.io'],
            [
                'name' => 'Admin User',
                'email' => 'admin@monetro.io',
                'password' => Hash::make('Admin123!'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'timezone' => 'Asia/Bangkok',
            ]
        );

        // Create Moderator
        User::firstOrCreate(
            ['email' => 'moderator@monetro.io'],
            [
                'name' => 'Moderator User',
                'email' => 'moderator@monetro.io',
                'password' => Hash::make('Moderator123!'),
                'role' => User::ROLE_MODERATOR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'timezone' => 'Asia/Bangkok',
            ]
        );

        // Create Test User
        User::firstOrCreate(
            ['email' => 'user@monetro.io'],
            [
                'name' => 'Test User',
                'email' => 'user@monetro.io',
                'password' => Hash::make('User123!'),
                'role' => User::ROLE_USER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'timezone' => 'Asia/Bangkok',
            ]
        );

        $this->command->info('Admin users created successfully!');
        $this->command->table(
            ['Email', 'Role', 'Password'],
            [
                ['superadmin@monetro.io', 'Super Admin', 'SuperAdmin123!'],
                ['admin@monetro.io', 'Admin', 'Admin123!'],
                ['moderator@monetro.io', 'Moderator', 'Moderator123!'],
                ['user@monetro.io', 'User', 'User123!'],
            ]
        );
    }
}