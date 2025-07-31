<?php

// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\UserController;
// use App\Http\Controllers\Api\RoleController;

// API Authentication (if using Breeze API)
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user()->load('roles');
});

// Public API routes
// Route::prefix('auth')->group(function () {
//     Route::post('login', [AuthController::class, 'login']);
//     Route::post('register', [AuthController::class, 'register']);
// });

// // Protected API routes
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('auth/logout', [AuthController::class, 'logout']);
//     Route::get('profile', [AuthController::class, 'profile']);
//     Route::put('profile', [AuthController::class, 'updateProfile']);

//     // Admin API routes
//     Route::middleware('role:admin,super_admin')->group(function () {
//         Route::apiResource('users', UserController::class);
//         Route::put('users/{user}/activate', [UserController::class, 'activate']);
//         Route::put('users/{user}/deactivate', [UserController::class, 'deactivate']);
//         Route::put('users/{user}/roles', [UserController::class, 'updateRoles']);
//     });

//     // Super Admin API routes
//     Route::middleware('role:super_admin')->group(function () {
//         Route::apiResource('roles', RoleController::class);
//         Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions']);
//     });
// });
