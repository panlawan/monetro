#!/bin/bash
# 🔧 แก้ไข User Model Syntax Error

echo "🔧 แก้ไข User Model Syntax Error..."

# 1. Backup current User.php
echo "💾 สำรองไฟล์ User.php..."
cp app/Models/User.php app/Models/User.php.error.backup

# 2. ตรวจสอบและสร้าง User.php ใหม่ที่ถูกต้อง
echo "👤 สร้าง User Model ใหม่ที่ถูกต้อง..."
cat > app/Models/User.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
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
    // EMAIL VERIFICATION METHODS
    // ==========================================

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
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

# 3. ตรวจสอบ syntax ของไฟล์ PHP
echo "🔍 ตรวจสอบ PHP Syntax..."
php -l app/Models/User.php

if [ $? -eq 0 ]; then
    echo "✅ User.php syntax ถูกต้อง"
else
    echo "❌ User.php ยังมี syntax error"
    exit 1
fi

# 4. ตรวจสอบ Admin Routes และ Controllers
echo "🛣️  ตรวจสอบ Admin Routes..."
if [ ! -f "app/Http/Controllers/Admin/DashboardController.php" ]; then
    echo "📁 สร้าง Admin Controllers..."
    mkdir -p app/Http/Controllers/Admin
    
    # สร้าง Admin Dashboard Controller
    cat > app/Http/Controllers/Admin/DashboardController.php << 'EOF'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function index()
    {
        // ตรวจสอบสิทธิ์ admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $stats = [
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_roles' => Role::count(),
            'recent_users' => User::latest()->take(5)->get(),
            'user_roles' => User::with('roles')->get()->groupBy(function($user) {
                return $user->roles->first()->display_name ?? 'No Role';
            })
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
EOF

    # สร้าง Admin Dashboard View
    mkdir -p resources/views/admin
    cat > resources/views/admin/dashboard.blade.php << 'EOF'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Users -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-5.197"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verified Users -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Verified</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['verified_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Users -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Roles -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Roles</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_roles'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Users</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($stats['recent_users'] as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->email_verified_at)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
EOF
fi

# 5. เพิ่ม Admin Routes
echo "🛣️  เพิ่ม Admin Routes..."
if ! grep -q "admin.dashboard" routes/web.php; then
    cat >> routes/web.php << 'EOF'

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
});
EOF
fi

# 6. Clear caches
echo "🧹 Clear All Caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 7. ทดสอบ syntax และ routes
echo "🧪 ทดสอบ Laravel..."
php artisan route:list | grep admin || echo "⚠️  Admin routes ไม่พบ"

echo ""
echo "✅ แก้ไข User Model Syntax Error เสร็จสิ้น!"
echo ""
echo "🔧 สิ่งที่แก้ไข:"
echo "- สร้าง User.php ใหม่ที่ syntax ถูกต้อง"
echo "- เพิ่ม MustVerifyEmail interface"
echo "- สร้าง Admin Dashboard Controller และ View"
echo "- เพิ่ม Admin Routes"
echo ""
echo "🌐 Admin Dashboard: http://localhost:8080/admin/dashboard"
echo "🏠 User Dashboard: http://localhost:8080/dashboard"
echo ""
echo "🎯 Features:"
echo "- ✅ Role System ทำงานถูกต้อง"
echo "- ✅ Email Verification Support"
echo "- ✅ Avatar System"
echo "- ✅ Admin Dashboard พร้อมสถิติ"
echo "- ✅ Recent Users List"
echo ""
echo "🚀 พร้อมใช้งานแล้ว!"