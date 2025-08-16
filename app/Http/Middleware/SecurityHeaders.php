<?php
// app/Http/Middleware/SecurityHeaders.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Content Security Policy ที่อนุญาต Chart.js
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: blob:; " .
               "connect-src 'self'; " .
               "worker-src 'self'; " .
               "frame-src 'none'; " .
               "object-src 'none'; " .
               "base-uri 'self'";

        $response->headers->set('Content-Security-Policy', $csp);
        
        // Other security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // ปิด Pragma header (deprecated)
        $response->headers->remove('Pragma');
        
        // ใช้ Cache-Control แทน Expires
        if (!$response->headers->has('Cache-Control')) {
            $response->headers->set('Cache-Control', 'no-cache, private');
        }

        return $response;
    }
}