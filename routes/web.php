<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

Route::get('/', function () {
    return view('welcome');
});

// Health check endpoint for production monitoring
Route::get('/health', function () {
    try {
        // Check database connection
        DB::connection()->getPdo();
        
        // Check Redis connection
        Cache::store('redis')->put('health_check', 'ok', 60);
        
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'database' => 'connected',
            'redis' => 'connected',
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
            'timestamp' => now(),
        ], 503);
    }
});