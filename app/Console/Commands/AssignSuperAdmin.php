<?php

// app/Console/Commands/AssignSuperAdmin.php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class AssignSuperAdmin extends Command
{
    protected $signature = 'user:make-super-admin {user_id}';

    protected $description = 'Assign Super Admin role to a user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (! $user) {
            $this->error("User ID {$userId} not found!");

            return 1;
        }

        $superAdminRole = Role::where('name', 'super_admin')->first();

        if (! $superAdminRole) {
            $this->error('Super Admin role not found!');

            return 1;
        }

        // ลบ roles เก่า
        $user->roles()->detach();

        // กำหนด super admin
        $user->roles()->attach($superAdminRole->id, [
            'assigned_at' => now(),
            'assigned_by' => auth()->id() ?? 1,
        ]);

        $this->info("✅ {$user->name} ({$user->email}) is now a Super Admin!");

        return 0;
    }
}
