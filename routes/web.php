<?php

// routes/web.php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    return view('dashboard', compact('user'));
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User management routes
    Route::resource('users', AdminUserController::class);

    // Additional user management routes
    Route::put('users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
    Route::put('users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
    Route::put('users/{user}/roles', [AdminUserController::class, 'updateRoles'])->name('users.roles');
    Route::post('users/bulk-action', [AdminUserController::class, 'bulkAction'])->name('users.bulk-action');

    // Impersonation routes (Super Admin only)
    Route::middleware('role:super_admin')->group(function () {
        Route::post('users/{user}/impersonate', [AdminUserController::class, 'impersonate'])->name('users.impersonate');
        //     // แก้ไข route สำหรับ stop impersonating
        //     Route::get('users/stop-impersonating', [AdminUserController::class, 'stopImpersonating'])->name('users.stop-impersonating');
        //     Route::post('users/stop-impersonating', [AdminUserController::class, 'stopImpersonating']);
    });
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

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('api/dashboard')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary'])->name('api.dashboard.summary');
        Route::get('/monthly', [DashboardController::class, 'monthly'])->name('api.dashboard.monthly');
    });

    Route::post('/preferences/theme', [DashboardController::class, 'setTheme'])->name('preferences.theme');
});

// Debug routes (remove in production)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/email/verify', [App\Http\Controllers\EmailVerificationController::class, 'show'])
        ->name('verification.notice');

    Route::post('/email/verification-notification', [App\Http\Controllers\EmailVerificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/debug/avatar/{user}', function (\App\Models\User $user) {
        return response()->json([
            'user_id' => $user->id,
            'avatar_db' => $user->avatar,
            'avatar_url' => $user->avatar_url ?? null,
            'storage_exists' => $user->avatar ? \Storage::disk('public')->exists(str_replace('storage/', '', $user->avatar)) : false,
            'storage_path' => $user->avatar ? storage_path('app/public/' . str_replace('storage/', '', $user->avatar)) : null,
            'public_path' => $user->avatar ? public_path('storage/' . str_replace('storage/', '', $user->avatar)) : null,
        ]);
    });

    Route::get('/debug/storage', function () {
        return response()->json([
            'storage_link_exists' => is_link(public_path('storage')),
            'storage_link_target' => readlink(public_path('storage')),
            'avatars_dir_exists' => is_dir(storage_path('app/public/avatars')),
            'avatars_writable' => is_writable(storage_path('app/public/avatars')),
            'public_storage_exists' => is_dir(public_path('storage')),
            'sample_files' => \Storage::disk('public')->files('avatars'),
        ]);
    });
    Route::get('/stop-impersonating', [AdminUserController::class, 'stopImpersonating'])->name('stop-impersonating');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // Route::prefix('api/dashboard')->group(function () {
    //     Route::get('/summary', [DashboardController::class, 'summary'])
    //         ->name('api.dashboard.summary');
    //     Route::get('/monthly', [DashboardController::class, 'monthly'])   // << เพิ่ม
    //         ->name('api.dashboard.monthly');
    // });

    // JSON endpoints สำหรับหน้า dashboard
    // Route::get('/api/dashboard/summary', [DashboardController::class, 'summary'])->name('api.dashboard.summary');
    // Route::get('/api/dashboard/charts/monthly', [DashboardController::class, 'monthlyChart'])->name('api.dashboard.monthly');

    // เปลี่ยนธีม (light/dark/auto)
    Route::post('/preferences/theme', [DashboardController::class, 'setTheme'])->name('preferences.theme');
});

// Test route สำหรับ Tailwind CSS
Route::get('/test-tailwind', function () {
    return view('test-tailwind');
})->name('test.tailwind');

require __DIR__ . '/auth.php';
