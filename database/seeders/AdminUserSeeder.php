<?php
// database/seeders/AdminUserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign Super Admin Role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole && !$superAdmin->hasRole('super_admin')) {
            $superAdmin->roles()->attach($superAdminRole->id, [
                'assigned_at' => now(),
                'assigned_by' => $superAdmin->id,
            ]);
        }

        // Create Regular Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin2@example.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin2@example.com',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign Admin Role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole->id, [
                'assigned_at' => now(),
                'assigned_by' => $superAdmin->id,
            ]);
        }
    }
}