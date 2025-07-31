<?php

// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access',
                'permissions' => ['*'],
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'System administration',
                'permissions' => ['users.create', 'users.read', 'users.update', 'users.delete'],
                'is_active' => true,
            ],
            [
                'name' => 'user',
                'display_name' => 'Regular User',
                'description' => 'Standard user access',
                'permissions' => ['profile.read', 'profile.update'],
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
