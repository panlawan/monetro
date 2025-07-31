#!/bin/bash
# 🔧 แก้ไข User Model - เพิ่ม hasRole() method

echo "🔧 แก้ไข User Model - เพิ่ม Role System..."

# 1. สร้าง User Model ที่สมบูรณ์พร้อม Role System
echo "👤 อัปเดต User Model ให้รองรับ Role System..."
cat > app/Models/User.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ==========================================
    // ROLE SYSTEM METHODS
    // ==========================================

    /**
     * Relationship: User belongsToMany Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot('assigned_at', 'assigned_by', 'expires_at')
                    ->withTimestamps();
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($roleName)
    {
        if (is_array($roleName)) {
            return $this->roles()->whereIn('name', $roleName)->exists();
        }
        
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user has all the given roles
     */
    public function hasAllRoles(array $roles)
    {
        return $this->roles()->whereIn('name', $roles)->count() === count($roles);
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission)
    {
        return $this->roles()->where('is_active', true)
                    ->get()
                    ->pluck('permissions')
                    ->flatten()
                    ->contains($permission);
    }

    /**
     * Assign role to user
     */
    public function assignRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id, [
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]);
        }
        return $this;
    }

    /**
     * Remove role from user
     */
    public function removeRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
        return $this;
    }

    /**
     * Sync user roles
     */
    public function syncRoles(array $roleNames)
    {
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->toArray();
        $this->roles()->sync($roleIds);
        return $this;
    }

    /**
     * Get all role names for the user
     */
    public function getRoleNamesAttribute()
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Check if user is admin (has admin or super_admin role)
     */
    public function isAdmin()
    {
        return $this->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }

    // ==========================================
    // AVATAR METHODS
    // ==========================================

    /**
     * Get avatar URL with proper path handling
     */
    public function getAvatarUrlAttribute()
    {
        \Log::info('Getting avatar URL', [
            'user_id' => $this->id,
            'avatar_path' => $this->avatar
        ]);

        if ($this->avatar) {
            // Clean path - remove any 'storage/' prefix if exists
            $cleanPath = str_replace('storage/', '', $this->avatar);
            
            // Check if file exists in storage
            if (Storage::disk('public')->exists($cleanPath)) {
                $url = asset('storage/' . $cleanPath);
                \Log::info('Avatar URL generated', ['url' => $url]);
                return $url;
            } else {
                \Log::warning('Avatar file not found', [
                    'path' => $cleanPath,
                    'full_path' => storage_path('app/public/' . $cleanPath)
                ]);
            }
        }
        
        // Default avatar using UI Avatars service
        $defaultUrl = 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=ffffff&size=200';
        \Log::info('Using default avatar', ['url' => $defaultUrl]);
        return $defaultUrl;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get user's primary role (first role)
     */
    public function getPrimaryRole()
    {
        return $this->roles()->first();
    }

    /**
     * Check if user account is active
     */
    public function isActive()
    {
        return $this->is_active ?? true;
    }

    /**
     * Activate user account
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    /**
     * Deactivate user account
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
        return $this;
    }
}
EOF

# 2. สร้าง Role Model
echo "🎭 สร้าง Role Model..."
cat > app/Models/Role.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->withPivot('assigned_at', 'assigned_by', 'expires_at')
                    ->withTimestamps();
    }

    // Helper Methods
    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    public function addPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
        return $this;
    }

    public function removePermission($permission)
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, function($p) use ($permission) {
            return $p !== $permission;
        });
        $this->update(['permissions' => array_values($permissions)]);
        return $this;
    }
}
EOF

# 3. สร้าง Migrations สำหรับ Role System
echo "🗃️  สร้าง Role System Migrations..."

# Roles table migration
cat > database/migrations/create_roles_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
EOF

# User roles pivot table migration
cat > database/migrations/create_user_roles_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
EOF

# Add missing columns to users table
cat > database/migrations/add_missing_columns_to_users_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('phone');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'phone', 'is_active', 'last_login_at']);
        });
    }
};
EOF

# 4. สร้าง Role Seeder
echo "🌱 สร้าง Role Seeder..."
mkdir -p database/seeders
cat > database/seeders/RoleSeeder.php << 'EOF'
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
                    'admin.access'
                ]
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator', 
                'description' => 'Administrator access',
                'permissions' => [
                    'users.create', 'users.read', 'users.update',
                    'admin.access'
                ]
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Moderator access',
                'permissions' => [
                    'users.read', 'users.update'
                ]
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Basic user access',
                'permissions' => [
                    'profile.read', 'profile.update'
                ]
            ]
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
            if ($superAdminRole && !$firstUser->hasRole('super_admin')) {
                $firstUser->roles()->attach($superAdminRole->id, [
                    'assigned_at' => now(),
                    'assigned_by' => $firstUser->id,
                ]);
            }
        }
    }
}
EOF

# 5. แก้ไข Navigation Layout ให้ทำงานได้
echo "🧭 แก้ไข Navigation Layout..."
if [ -f "resources/views/layouts/navigation.blade.php" ]; then
    # Backup original navigation
    cp resources/views/layouts/navigation.blade.php resources/views/layouts/navigation.blade.php.backup
    
    # Replace hasRole calls with proper null checks
    sed -i 's/@if(auth()->user()->hasRole('\''admin'\''))/@if(auth()->user() && method_exists(auth()->user(), '\''hasRole'\'') && auth()->user()->hasRole('\''admin'\''))/g' resources/views/layouts/navigation.blade.php
    sed -i 's/@if(auth()->user()->hasRole('\''super_admin'\''))/@if(auth()->user() && method_exists(auth()->user(), '\''hasRole'\'') && auth()->user()->hasRole('\''super_admin'\''))/g' resources/views/layouts/navigation.blade.php
    
    echo "✅ แก้ไข Navigation Layout"
else
    echo "⚠️  ไม่พบไฟล์ navigation.blade.php"
fi

# 6. Clear caches and run migrations
echo "🧹 Clear caches และรัน migrations..."
php artisan config:clear
php artisan route:clear  
php artisan view:clear
php artisan cache:clear

echo "🗃️  รัน migrations..."
php artisan migrate

echo "🌱 รัน role seeder..."
php artisan db:seed --class=RoleSeeder

echo ""
echo "✅ แก้ไข User Model และ Role System เสร็จสิ้น!"
echo ""
echo "🎯 สิ่งที่เพิ่มเข้ามา:"
echo "- User Model พร้อม Role System methods"
echo "- Role Model และ relationships"
echo "- Migrations สำหรับ roles และ user_roles tables"
echo "- Role Seeder พร้อมข้อมูลเริ่มต้น"
echo "- แก้ไข Navigation Layout"
echo ""
echo "📋 Methods ใหม่ใน User Model:"
echo "- hasRole(\$role)"
echo "- hasAnyRole([\$roles])"
echo "- isAdmin()"
echo "- isSuperAdmin()"
echo "- assignRole(\$role)"
echo "- removeRole(\$role)"
echo ""
echo "🌟 ทดสอบ:"
echo "1. เข้า /dashboard"
echo "2. ตรวจสอบ navigation menu"
echo "3. ใช้ php artisan tinker เพื่อทดสอบ roles"
echo ""
echo "🔧 Tinker Examples:"
echo 'User::first()->hasRole("admin")'
echo 'User::first()->assignRole("admin")'
echo 'User::first()->roles'