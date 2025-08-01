<?php
// app/Console/Commands/AssignSuperAdmin.php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class AssignSuperAdmin extends Command
{
    protected $signature = 'user:make-super-admin {user_id : The ID of the user} {--keep-roles : Keep existing roles instead of replacing them}';

    protected $description = 'Assign Super Admin role to a user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $keepRoles = $this->option('keep-roles');
        
        // Find user
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User ID {$userId} not found!");
            return 1;
        }

        // Find super admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if (!$superAdminRole) {
            $this->error('❌ Super Admin role not found! Please run seeders first.');
            $this->line('   Run: php artisan db:seed --class=RoleSeeder');
            return 1;
        }

        // Check if user already has super admin role
        if ($user->hasRole('super_admin')) {
            $this->info("ℹ️  {$user->name} ({$user->email}) is already a Super Admin!");
            return 0;
        }

        // Remove old roles only if --keep-roles is not specified
        if (!$keepRoles) {
            $oldRoles = $user->roles->pluck('display_name')->toArray();
            if (!empty($oldRoles)) {
                $this->line("🗑️  Removing existing roles: " . implode(', ', $oldRoles));
                $user->roles()->detach();
            }
        }

        // Assign super admin role
        $user->roles()->attach($superAdminRole->id, [
            'assigned_at' => now(),
            'assigned_by' => 1, // System user ID, or use auth()->id() ?? 1
        ]);

        $this->info("✅ {$user->name} ({$user->email}) is now a Super Admin!");
        
        // Show current roles
        $currentRoles = $user->fresh()->roles->pluck('display_name')->toArray();
        $this->line("📋 Current roles: " . implode(', ', $currentRoles));

        return 0;
    }
}