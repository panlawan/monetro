<?php

// routes/web.php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    return view('dashboard', compact('user'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User management (placeholder routes)
    Route::get('users', function () {
        return 'User Management Coming Soon';
    })->name('users.index');
    Route::get('users/create', function () {
        return 'Create User Coming Soon';
    })->name('users.create');
});

// Super Admin routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('roles', function () {
        return 'Role Management Coming Soon';
    })->name('roles.index');
});

// Test route สำหรับ Tailwind CSS
Route::get('/test-tailwind', function () {
    return view('test-tailwind');
})->name('test.tailwind');

require __DIR__.'/auth.php';

// Debug routes (remove in production)
Route::get('/debug/avatar/{user}', function(\App\Models\User $user) {
    return response()->json([
        'user_id' => $user->id,
        'avatar_db' => $user->avatar,
        'avatar_url' => $user->avatar_url,
        'storage_exists' => $user->avatar ? \Storage::disk('public')->exists(str_replace('storage/', '', $user->avatar)) : false,
        'storage_path' => $user->avatar ? storage_path('app/public/' . str_replace('storage/', '', $user->avatar)) : null,
        'public_path' => $user->avatar ? public_path('storage/' . str_replace('storage/', '', $user->avatar)) : null,
    ]);
})->middleware('auth');

Route::get('/debug/storage', function() {
    return response()->json([
        'storage_link_exists' => is_link(public_path('storage')),
        'storage_link_target' => readlink(public_path('storage')),
        'avatars_dir_exists' => is_dir(storage_path('app/public/avatars')),
        'avatars_writable' => is_writable(storage_path('app/public/avatars')),
        'public_storage_exists' => is_dir(public_path('storage')),
        'sample_files' => \Storage::disk('public')->files('avatars'),
    ]);
});
