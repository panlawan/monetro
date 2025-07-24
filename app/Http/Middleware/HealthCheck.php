<?php
// app/Http/Middleware/HealthCheck.php - Health Check Middleware

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthCheck
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->path() === 'health') {
            $health = [
                'status' => 'ok',
                'timestamp' => now()->toISOString(),
                'services' => []
            ];

            // Check database
            try {
                DB::connection()->getPdo();
                $health['services']['database'] = 'ok';
            } catch (\Exception $e) {
                $health['services']['database'] = 'error';
                $health['status'] = 'error';
            }

            // Check Redis
            try {
                Redis::ping();
                $health['services']['redis'] = 'ok';
            } catch (\Exception $e) {
                $health['services']['redis'] = 'error';
                $health['status'] = 'warning';
            }

            // Check storage
            if (is_writable(storage_path('logs'))) {
                $health['services']['storage'] = 'ok';
            } else {
                $health['services']['storage'] = 'error';
                $health['status'] = 'error';
            }

            $status = $health['status'] === 'ok' ? 200 : 503;
            return response()->json($health, $status);
        }

        return $next($request);
    }
}