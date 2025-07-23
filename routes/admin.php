<?php
// routes/admin.php

use Illuminate\Support\Facades\Route;

// Temporary admin routes until controllers are created
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Admin Dashboard (temporary)
    Route::get('/', function () {
        // Check if user can access admin
        if (!auth()->user()->canAccessAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
        
        return view('admin.temp-dashboard');
    })->name('dashboard');
    
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    });
    
    // Users routes (temporary)
    Route::get('/users', function () {
        if (!auth()->user()->canAccessAdmin()) {
            abort(403, 'Access denied.');
        }
        
        $users = \App\Models\User::latest()->paginate(15);
        return view('admin.temp-users', compact('users'));
    })->name('users.index');
    
});