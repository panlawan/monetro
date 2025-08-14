<?php

// app/Console/Commands/UpdateRolePermissions.php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;

class UpdateRolePermissions extends Command
{
    protected $signature = 'role:update-permissions {--force : Force update without confirmation}';

    protected $description = 'Update role permissions according to system requirements';

    public function handle()
    {
        $this->info('🔧 Updating Role Permissions...');

        $rolePermissions = [
            'super_admin' => [
                // User Management
                'users.create',
                'users.read',
                'users.update',
                'users.delete',
                'users.activate',
                'users.deactivate',
                'users.assign-roles',

                // Role Management
                'roles.create',
                'roles.read',
                'roles.update',
                'roles.delete',
                'roles.assign-permissions',

                // Content Management
                'content.create',
                'content.read',
                'content.update',
                'content.delete',
                'content.publish',
                'content.unpublish',

                // System Settings
                'settings.read',
                'settings.update',
                'settings.backup',
                'settings.restore',

                // Reports & Analytics
                'reports.read',
                'reports.create',
                'reports.export',

                // System Administration
                'system.logs',
                'system.maintenance',
                'system.cache-clear',
                'system.queue-monitor',

                // Profile Management
                'profile.read',
                'profile.update',
            ],

            'admin' => [
                // User Management (Limited)
                'users.create',
                'users.read',
                'users.update',
                'users.activate',
                'users.deactivate',

                // Content Management
                'content.create',
                'content.read',
                'content.update',
                'content.delete',
                'content.publish',

                // Basic Settings
                'settings.read',

                // Reports
                'reports.read',
                'reports.create',

                // Profile Management
                'profile.read',
                'profile.update',
            ],

            'moderator' => [
                // Limited User Management
                'users.read',
                'users.update',

                // Content Moderation
                'content.read',
                'content.update',
                'content.publish',
                'content.unpublish',

                // Basic Reports
                'reports.read',

                // Profile Management
                'profile.read',
                'profile.update',
            ],

            'user' => [
                // Basic Profile Access
                'profile.read',
                'profile.update',

                // Basic Content (own content only)
                'content.read',
                'content.create',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                $this->warn("⚠️  Role '{$roleName}' not found, skipping...");

                continue;
            }

            if (! $this->option('force')) {
                $currentPermissions = $role->permissions ?? [];
                $this->line("📋 Current permissions for {$role->display_name}:");
                $this->line('   '.implode(', ', $currentPermissions));
                $this->line('🔄 New permissions:');
                $this->line('   '.implode(', ', $permissions));

                if (! $this->confirm("Update permissions for {$role->display_name}?")) {
                    $this->line("⏭️  Skipped {$role->display_name}");

                    continue;
                }
            }

            $role->update(['permissions' => $permissions]);
            $this->info("✅ Updated permissions for {$role->display_name} (".count($permissions).' permissions)');
        }

        $this->info('🎉 Role permissions update completed!');

        return Command::SUCCESS;
    }
}
