<?php
// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all administrative privileges',
                'permissions' => [
                    // User Management
                    'users.create', 'users.read', 'users.update', 'users.delete',
                    'users.activate', 'users.deactivate', 'users.assign-roles',
                    
                    // Role Management  
                    'roles.create', 'roles.read', 'roles.update', 'roles.delete',
                    'roles.assign-permissions',
                    
                    // Content Management
                    'content.create', 'content.read', 'content.update', 'content.delete',
                    'content.publish', 'content.unpublish',
                    
                    // System Settings
                    'settings.read', 'settings.update', 'settings.backup', 'settings.restore',
                    
                    // Reports & Analytics
                    'reports.read', 'reports.create', 'reports.export',
                    
                    // System Administration
                    'system.logs', 'system.maintenance', 'system.cache-clear', 'system.queue-monitor',
                    
                    // Profile Management
                    'profile.read', 'profile.update'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator', 
                'description' => 'Administrator access with user and content management',
                'permissions' => [
                    // User Management (Limited)
                    'users.create', 'users.read', 'users.update', 
                    'users.activate', 'users.deactivate',
                    
                    // Content Management
                    'content.create', 'content.read', 'content.update', 'content.delete',
                    'content.publish',
                    
                    // Basic Settings
                    'settings.read',
                    
                    // Reports
                    'reports.read', 'reports.create',
                    
                    // Profile Management
                    'profile.read', 'profile.update'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Moderator access for content management and user oversight',
                'permissions' => [
                    // Limited User Management
                    'users.read', 'users.update',
                    
                    // Content Moderation
                    'content.read', 'content.update', 
                    'content.publish', 'content.unpublish',
                    
                    // Basic Reports
                    'reports.read',
                    
                    // Profile Management
                    'profile.read', 'profile.update'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Basic user access for profile and own content management',
                'permissions' => [
                    // Basic Profile Access
                    'profile.read', 'profile.update',
                    
                    // Basic Content (own content only)
                    'content.read', 'content.create'
                ],
                'is_active' => true,
            ]
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
            
            $this->command->info("✅ Role '{$roleData['display_name']}' created/updated with " . count($roleData['permissions']) . " permissions");
        }
    }
}