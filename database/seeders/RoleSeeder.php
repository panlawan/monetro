<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create basic roles
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access',
                'permissions' => [
                    'users.create', 'users.read', 'users.update', 'users.delete',
                    'roles.create', 'roles.read', 'roles.update', 'roles.delete',
                    'admin.access',
                ],
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Administrator access',
                'permissions' => [
                    'users.create', 'users.read', 'users.update',
                    'admin.access',
                ],
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Moderator access',
                'permissions' => [
                    'users.read', 'users.update',
                ],
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Basic user access',
                'permissions' => [
                    'profile.read', 'profile.update',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        // Assign super_admin role to first user if exists
        $firstUser = User::first();
        if ($firstUser) {
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole && ! $firstUser->hasRole('super_admin')) {
                $firstUser->roles()->attach($superAdminRole->id, [
                    'assigned_at' => now(),
                    'assigned_by' => $firstUser->id,
                ]);
            }
        }
    }
}
