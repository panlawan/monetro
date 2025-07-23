<?php
// app/Http/Middleware/TrackLastLogin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track login for authenticated users
        if ($request->user() && !session()->has('login_tracked')) {
            $request->user()->recordLogin();
            session(['login_tracked' => true]);
        }

        return $response;
    }
}