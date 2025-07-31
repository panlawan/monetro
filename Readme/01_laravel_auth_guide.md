# Laravel Breeze + Role System - Complete Guide

> 🔐 คู่มือสร้างระบบ Authentication เริ่มจาก Laravel Breeze แล้วขยายเป็นระบบ Role-Based Access Control (RBAC)

## 📋 Table of Contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Step 1: Install Laravel Breeze](#step-1--install-laravel-breeze)
- [Step 2: Extend Database Schema](#step-2--extend-database-schema)
- [Step 3: Create Models & Relationships](#step-3--create-models--relationships)
- [Step 4: Create Database Seeders](#step-4--create-database-seeders)
- [Step 5: Extend Authentication Logic](#step-5--extend-authentication-logic)
- [Step 6: Create Role Middleware](#step-6--create-role-middleware)
- [Step 7: Update Routes](#step-7--update-routes)
- [Step 8: Create Admin Controllers](#step-8--create-admin-controllers)
- [Step 9: Update Views](#step-9--update-views)
- [Step 10: Testing](#step-10--testing)
- [Usage Examples](#usage-examples)
- [Security Best Practices](#security-best-practices)

## Overview

การพัฒนานี้จะให้คุณมี:

- ✅ Laravel Breeze Authentication (Login/Register/Password Reset)
- ✅ Role-Based Access Control (RBAC)
- ✅ Permission Management
- ✅ User Management สำหรับ Admin
- ✅ Account Activation/Deactivation
- ✅ Extended User Profile
- ✅ Complete Admin Panel
- ✅ API Support

## Prerequisites

- Laravel 12.x with Docker Environment
- PHP 8.2+
- MySQL 8.0
- Node.js (สำหรับ Breeze frontend)

## Step 1: 🚀 Install Laravel Breeze

### 1.1 Install Breeze Package

```bash
# เข้าสู่ container
make shell

# Install Laravel Breeze
composer require laravel/breeze --dev
```

### 1.2 Install Breeze Blade Stack

```bash
# Install Blade stack with Tailwind CSS
php artisan breeze:install blade

# หรือ Install API stack (ถ้าต้องการ SPA)
# php artisan breeze:install api
```

### 1.3 Install Frontend Dependencies

```bash
# Install NPM dependencies
npm install

# Build assets
npm run dev

# หรือสำหรับ production
# npm run build
```

### 1.4 Run Migrations

```bash
# Run default Breeze migrations
php artisan migrate
```

### 1.5 ทดสอบ Breeze

เปิดเบราว์เซอร์ไปที่ `http://localhost:8080` และทดสอบ:
- สมัครสมาชิก
- เข้าสู่ระบบ
- แก้ไขโปรไฟล์

## Step 2: 📊 Extend Database Schema

### 2.1 สร้าง Migration Files

```bash
# สร้าง migrations สำหรับ Role System
php artisan make:migration add_fields_to_users_table
php artisan make:migration create_roles_table
php artisan make:migration create_user_roles_table
```

### 2.2 ปรับปรุง Users Table

```php
<?php
// database/migrations/xxxx_add_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes(); // deleted_at for soft delete
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'avatar', 'is_active', 'last_login_at']);
            $table->dropSoftDeletes();
        });
    }
};
```

### 2.3 สร้าง Roles Table

```php
<?php
// database/migrations/xxxx_create_roles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'admin', 'user', 'moderator'
            $table->string('display_name'); // 'Administrator', 'Regular User'
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // ['users.create', 'users.read']
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
```

### 2.4 สร้าง User_Roles Table (Pivot)

```php
<?php
// database/migrations/xxxx_create_user_roles_table.php

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
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
```

### 2.5 Run New Migrations

```bash
php artisan migrate
```

## Step 3: 🏗️ Create Models & Relationships

### 3.1 ปรับปรุง User Model

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active'
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

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot('assigned_at', 'assigned_by', 'expires_at')
                    ->withTimestamps();
    }

    // Helper Methods
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasAnyRole(array $roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission($permission)
    {
        return $this->roles()->where('is_active', true)
                    ->get()
                    ->pluck('permissions')
                    ->flatten()
                    ->contains($permission);
    }

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

    public function removeRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
        return $this;
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }
}
```

### 3.2 สร้าง Role Model

```bash
php artisan make:model Role
```

```php
<?php
// app/Models/Role.php

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
        return in_array('*', $permissions) || in_array($permission, $permissions);
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
        $permissions = array_diff($permissions, [$permission]);
        $this->update(['permissions' => array_values($permissions)]);
        return $this;
    }
}
```

## Step 4: 🌱 Create Database Seeders

### 4.1 สร้าง Role Seeder

```bash
php artisan make:seeder RoleSeeder
```

```php
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
                'description' => 'Full system access with all permissions',
                'permissions' => ['*'], // All permissions
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'System administration access',
                'permissions' => [
                    'users.create', 'users.read', 'users.update', 'users.delete',
                    'roles.read', 'roles.update',
                    'content.create', 'content.read', 'content.update', 'content.delete'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Content moderation access',
                'permissions' => [
                    'content.read', 'content.update', 'content.delete',
                    'users.read'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'user',
                'display_name' => 'Regular User',
                'description' => 'Standard user access',
                'permissions' => [
                    'profile.read', 'profile.update',
                    'content.read'
                ],
                'is_active' => true,
            ]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
```

### 4.2 สร้าง Admin User Seeder

```bash
php artisan make:seeder AdminUserSeeder
```

```php
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
```

### 4.3 อัปเดต DatabaseSeeder

```php
<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
```

### 4.4 Run Seeders

```bash
php artisan db:seed
```

## Step 5: 🔐 Extend Authentication Logic

### 5.1 ปรับปรุง RegisteredUserController

```php
<?php
// app/Http/Controllers/Auth/RegisteredUserController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        // Assign default role
        $defaultRole = Role::where('name', 'user')->first();
        if ($defaultRole) {
            $user->roles()->attach($defaultRole->id, [
                'assigned_at' => now(),
                'assigned_by' => null, // self-registered
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
```

### 5.2 ปรับปรุง AuthenticatedSessionController

```php
<?php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Check if user is active before authentication
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if ($user && !$user->is_active) {
            return back()->withErrors([
                'email' => 'บัญชีของคุณถูกปิดใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            ])->onlyInput('email');
        }

        $request->authenticate();

        $request->session()->regenerate();

        // Update last login time
        Auth::user()->update(['last_login_at' => now()]);

        // Redirect based on user role
        return $this->redirectBasedOnRole(Auth::user());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->hasRole('moderator')) {
            return redirect()->intended(route('moderator.dashboard'));
        } else {
            return redirect()->intended(route('dashboard'));
        }
    }
}
```

### 5.3 สร้าง Extended Profile Controller

```bash
php artisan make:controller ProfileController
```

```php
<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('roles'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        $validatedData = $request->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete(str_replace('storage/', '', $user->avatar));
            }

            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = $avatar->storeAs('avatars', $avatarName, 'public');
            $validatedData['avatar'] = 'storage/' . $avatarPath;
        }

        $user->fill($validatedData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
```

### 5.4 สร้าง ProfileUpdateRequest

```bash
php artisan make:request ProfileUpdateRequest
```

```php
<?php
// app/Http/Requests/ProfileUpdateRequest.php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}
```

## Step 6: 🛡️ Create Role Middleware

### 6.1 สร้าง CheckRole Middleware

```bash
php artisan make:middleware CheckRole
```

```php
<?php
// app/Http/Middleware/CheckRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account disabled'], 403);
            }
            return redirect()->route('login')->with('error', 'บัญชีของคุณถูกปิดใช้งาน');
        }

        // Check roles
        if (!$user->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Insufficient permissions'], 403);
            }
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return $next($request);
    }
}
```

### 6.2 สร้าง CheckPermission Middleware

```bash
php artisan make:middleware CheckPermission
```

```php
<?php
// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account disabled'], 403);
            }
            return redirect()->route('login')->with('error', 'บัญชีของคุณถูกปิดใช้งาน');
        }

        // Check permission
        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Permission denied'], 403);
            }
            abort(403, 'คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        return $next($request);
    }
}
```

### 6.3 ลงทะเบียน Middleware

```php
// bootstrap/app.php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

## Step 7: 🛣️ Update Routes

### 7.1 อัปเดต Web Routes

```php
<?php
// routes/web.php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Default dashboard (from Breeze)
Route::get('/dashboard', function () {
    $user = auth()->user()->load('roles');
    return view('dashboard', compact('user'));
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes (extended from Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User management
    Route::resource('users', AdminUserController::class);
    Route::put('users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
    Route::put('users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
    Route::put('users/{user}/roles', [AdminUserController::class, 'updateRoles'])->name('users.roles');
});

// Super Admin routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', AdminRoleController::class);
    Route::put('roles/{role}/permissions', [AdminRoleController::class, 'updatePermissions'])->name('roles.permissions');
});

// Moderator routes
Route::middleware(['auth', 'role:moderator,admin,super_admin'])->prefix('moderator')->name('moderator.')->group(function () {
    Route::get('/dashboard', function () {
        return view('moderator.dashboard');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
```

### 7.2 อัปเดต API Routes

```php
<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;

// API Authentication (if using Breeze API)
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user()->load('roles');
});

// Public API routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);

    // Admin API routes
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::put('users/{user}/activate', [UserController::class, 'activate']);
        Route::put('users/{user}/deactivate', [UserController::class, 'deactivate']);
        Route::put('users/{user}/roles', [UserController::class, 'updateRoles']);
    });

    // Super Admin API routes
    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions']);
    });
});
```

## Step 8: 🎛️ Create Admin Controllers

### 8.1 สร้าง Admin Dashboard Controller

```bash
php artisan make:controller Admin/DashboardController
```

```php
<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'total_roles' => Role::count(),
            'recent_users' => User::with('roles')->latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
```

### 8.2 สร้าง Admin User Controller

```bash
php artisan make:controller Admin/UserController --resource
```

```php
<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search functionality
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status);
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        $roles = Role::where('is_active', true)->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(), // Auto verify admin-created users
        ]);

        // Assign roles
        if ($request->roles) {
            foreach ($request->roles as $roleId) {
                $user->roles()->attach($roleId, [
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('admin.users.index')
                        ->with('success', 'สร้างผู้ใช้สำเร็จ');
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::where('is_active', true)->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Update roles
        $user->roles()->detach();
        if ($request->roles) {
            foreach ($request->roles as $roleId) {
                $user->roles()->attach($roleId, [
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('admin.users.index')
                        ->with('success', 'อัปเดตผู้ใช้สำเร็จ');
    }

    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                            ->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'ลบผู้ใช้สำเร็จ');
    }

    public function activate(User $user)
    {
        $user->update(['is_active' => true]);

        return redirect()->back()
                        ->with('success', 'เปิดใช้งานบัญชีสำเร็จ');
    }

    public function deactivate(User $user)
    {
        // Prevent deactivating own account
        if ($user->id === auth()->id()) {
            return redirect()->back()
                            ->with('error', 'ไม่สามารถปิดใช้งานบัญชีของตัวเองได้');
        }

        $user->update(['is_active' => false]);

        return redirect()->back()
                        ->with('success', 'ปิดใช้งานบัญชีสำเร็จ');
    }

    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->roles()->detach();

        if ($request->roles) {
            foreach ($request->roles as $roleId) {
                $user->roles()->attach($roleId, [
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->back()
                        ->with('success', 'อัปเดต Role สำเร็จ');
    }
}
```

### 8.3 สร้าง Admin Role Controller

```bash
php artisan make:controller Admin/RoleController --resource
```

```php
<?php
// app/Http/Controllers/Admin/RoleController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $permissions = [
        'users.create', 'users.read', 'users.update', 'users.delete',
        'roles.create', 'roles.read', 'roles.update', 'roles.delete',
        'content.create', 'content.read', 'content.update', 'content.delete',
        'settings.read', 'settings.update',
        'reports.read', 'reports.create',
    ];

    public function index()
    {
        $roles = Role::withCount('users')->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    public function show(Role $role)
    {
        $role->load('users');
        return view('admin.roles.show', compact('role'));
    }

    public function create()
    {
        $permissions = $this->permissions;
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.roles.index')
                        ->with('success', 'สร้าง Role สำเร็จ');
    }

    public function edit(Role $role)
    {
        $permissions = $this->permissions;
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.roles.index')
                        ->with('success', 'อัปเดต Role สำเร็จ');
    }

    public function destroy(Role $role)
    {
        // Prevent deleting if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                            ->with('error', 'ไม่สามารถลบ Role ที่มีผู้ใช้ได้');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
                        ->with('success', 'ลบ Role สำเร็จ');
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $role->update([
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->back()
                        ->with('success', 'อัปเดต Permissions สำเร็จ');
    }
}
```

## Step 9: 🎨 Update Views

### 9.1 อัปเดต Register Form

```blade
{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
```

### 9.2 อัปเดต Dashboard

```blade
{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">สวัสดี, {{ $user->name }}!</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            เข้าสู่ระบบล่าสุด: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'ไม่เคยเข้าสู่ระบบ' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- User Info -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3">ข้อมูลผู้ใช้</h4>
                            <div class="space-y-2 text-sm">
                                <div><strong>อีเมล:</strong> {{ $user->email }}</div>
                                <div><strong>โทรศัพท์:</strong> {{ $user->phone ?? 'ไม่ระบุ' }}</div>
                                <div><strong>สถานะ:</strong> 
                                    <span class="px-2 py-1 rounded text-xs {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Roles -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3">บทบาท (Roles)</h4>
                            <div class="space-y-2">
                                @forelse($user->roles as $role)
                                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                        {{ $role->display_name }}
                                    </span>
                                @empty
                                    <p class="text-sm text-gray-500">ไม่มีบทบาท</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6">
                        <h4 class="font-medium mb-3">การดำเนินการ</h4>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('profile.edit') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                แก้ไขโปรไฟล์
                            </a>
                            
                            @if($user->hasRole('admin') || $user->hasRole('super_admin'))
                                <a href="{{ route('admin.dashboard') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Admin Panel
                                </a>
                            @endif

                            @if($user->hasRole('moderator'))
                                <a href="{{ route('moderator.dashboard') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    Moderator Panel
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### 9.3 อัปเดต Profile Edit

```blade
{{-- resources/views/profile/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Information -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Profile Information') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Update your account's profile information and email address.") }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <!-- Avatar -->
                            <div>
                                <x-input-label for="avatar" :value="__('Avatar')" />
                                <div class="mt-2 flex items-center space-x-4">
                                    <img class="h-16 w-16 rounded-full object-cover" src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                                    <input type="file" name="avatar" id="avatar" accept="image/*" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                            </div>

                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Email -->
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div>
                                        <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                                            {{ __('Your email address is unverified.') }}

                                            <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                {{ __('Click here to re-send the verification email.') }}
                                            </button>
                                        </p>

                                        @if (session('status') === 'verification-link-sent')
                                            <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                                {{ __('A new verification link has been sent to your email address.') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Phone -->
                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <!-- Roles (Read Only) -->
                            <div>
                                <x-input-label :value="__('Roles')" />
                                <div class="mt-2 space-y-2">
                                    @forelse($user->roles as $role)
                                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                            {{ $role->display_name }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-gray-500">ไม่มีบทบาท</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>

                                @if (session('status') === 'profile-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >{{ __('Saved.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <!-- Update Password -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### 9.4 สร้าง Admin Dashboard View

```bash
mkdir -p resources/views/admin
```

```blade
{{-- resources/views/admin/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ผู้ใช้ทั้งหมด</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ใช้งานอยู่</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['active_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 008.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ปิดใช้งาน</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['inactive_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">บทบาททั้งหมด</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total_roles'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">การดำเนินการด่วน</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded text-center">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path>
                                </svg>
                                เพิ่มผู้ใช้ใหม่
                            </div>
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-4 rounded text-center">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                จัดการผู้ใช้
                            </div>
                        </a>

                        @if(auth()->user()->hasRole('super_admin'))
                        <a href="{{ route('admin.roles.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded text-center">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                </svg>
                                จัดการบทบาท
                            </div>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">ผู้ใช้ล่าสุด</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ผู้ใช้</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">บทบาท</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">สถานะ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">วันที่สร้าง</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($stats['recent_users'] as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img class="h-8 w-8 rounded-full" src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($user->roles as $role)
                                                <span class="inline-block px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                                    {{ $role->display_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $user->created_at->diffForHumans() }}
                                    </td>
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
```

### 9.5 อัปเดต Navigation Layout

```blade
{{-- resources/views/layouts/navigation.blade.php --}}
<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin'))
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                            {{ __('Admin Panel') }}
                        </x-nav-link>
                    @endif

                    @if(auth()->user()->hasRole('moderator'))
                        <x-nav-link :href="route('moderator.dashboard')" :active="request()->routeIs('moderator.*')">
                            {{ __('Moderator Panel') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <img class="h-8 w-8 rounded-full mr-2" src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- User Info -->
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                @foreach(Auth::user()->roles as $role)
                                    {{ $role->display_name }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin'))
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                    {{ __('Admin Panel') }}
                </x-responsive-nav-link>
            @endif

            @if(auth()->user()->hasRole('moderator'))
                <x-responsive-nav-link :href="route('moderator.dashboard')" :active="request()->routeIs('moderator.*')">
                    {{ __('Moderator Panel') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
```

## Step 10: 🧪 Testing

### 10.1 สร้าง Feature Tests

```bash
php artisan make:test BreezeAuthExtensionTest
```

```php
<?php
// tests/Feature/BreezeAuthExtensionTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class BreezeAuthExtensionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_user_registration_assigns_default_role()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0812345678',
        ]);

        $response->assertRedirect('/dashboard');
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_login_updates_last_login_time()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'last_login_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_admin_redirected_to_admin_dashboard()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        
        $adminRole = Role::where('name', 'admin')->first();
        $user->roles()->attach($adminRole->id);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_role_middleware_blocks_unauthorized_access()
    {
        $user = User::factory()->create();
        $userRole = Role::where('name', 'user')->first();
        $user->roles()->attach($userRole->id);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        
        $response->assertStatus(403);
    }

    public function test_profile_can_be_updated_with_avatar()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->patch('/profile', [
                            'name' => 'Updated Name',
                            'email' => $user->email,
                            'phone' => '0987654321',
                        ]);

        $response->assertRedirect('/profile');
        
        $this->assertEquals('Updated Name', $user->fresh()->name);
        $this->assertEquals('0987654321', $user->fresh()->phone);
    }

    public function test_user_roles_are_displayed_in_dashboard()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $user->roles()->attach($adminRole->id);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee($adminRole->display_name);
    }
}
```

### รันการทดสอบ

```bash
# รันทุก tests
php artisan test

# รัน specific test
php artisan test tests/Feature/BreezeAuthExtensionTest.php

# รัน tests พร้อม coverage
php artisan test --coverage
```

## Usage Examples

### ตัวอย่างการใช้งานใน Controller

```php
// ตรวจสอบ role
if (auth()->user()->hasRole('admin')) {
    // Admin logic
}

// ตรวจสอบ permission
if (auth()->user()->hasPermission('users.create')) {
    // Allow user creation
}

// ตรวจสอบหลาย roles
if (auth()->user()->hasAnyRole(['admin', 'moderator'])) {
    // Admin or moderator logic
}

// กำหนด role ให้ user
$user->assignRole('admin');

// ลบ role จาก user
$user->removeRole('moderator');
```

### ตัวอย่างการใช้งานใน Blade

```blade
{{-- ตรวจสอบ role --}}
@if(auth()->user()->hasRole('admin'))
    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
@endif

{{-- ตรวจสอบ permission --}}
@if(auth()->user()->hasPermission('users.create'))
    <button>Create User</button>
@endif

{{-- แสดง avatar --}}
<img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
```

### ตัวอย่าง API Usage

```javascript
// Login with extended response
const response = await fetch('/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password123'
    })
});
```

## Security Best Practices

### 🔐 เพิ่มเติมจาก Breeze

1. **Account Lockout**
```php
// ใน config/auth.php
'throttle' => [
    'max_attempts' => 5,
    'decay_minutes' => 15,
],
```

2. **Avatar Security**
```php
// Validate file type and size
'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
```

3. **Role Assignment Security**
```php
// ป้องกันการแก้ไข role ของตัวเอง
if ($user->id === auth()->id()) {
    throw new \Exception('Cannot modify own roles');
}
```

## การ Deploy

### Pre-deployment Checklist

```bash
# 1. Build assets
npm run build

# 2. Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Run migrations
php artisan migrate --force

# 5. Seed default data
php artisan db:seed --class=RoleSeeder
```

### Environment Variables

```bash
# .env production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Avatar storage
FILESYSTEM_DISK=public
```

## Conclusion

ระบบนี้ให้คุณมี Laravel Breeze พื้นฐาน + Role System ที่สมบูรณ์:

### ✅ **จาก Breeze:**
- Login/Register/Logout
- Password Reset
- Email Verification
- Profile Management
- Tailwind CSS UI

### ✅ **เพิ่มเติม:**
- Role-Based Access Control
- Permission Management
- User Management (Admin)
- Extended Profile (Avatar, Phone)
- Account Activation/Deactivation
- Role-based Navigation
- Admin Dashboard
- Complete Testing

### 🚀 **ขั้นตอนถัดไป:**
1. ปรับแต่ง Views ตามการออกแบบ
2. เพิ่ม Features เฉพาะ business
3. สร้าง API endpoints เพิ่มเติม
4. เพิ่ม Notifications
5. เพิ่ม Activity Logging

---

**🎉 ขอให้สนุกกับการพัฒนา Laravel Breeze + Role System!**