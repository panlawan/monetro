<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Update last_login_at only once per session to avoid too many DB updates
            if (!$request->session()->has('last_login_updated')) {
                $user->update(['last_login_at' => now()]);
                $request->session()->put('last_login_updated', true);
            }
        }

        return $next($request);
    }
}
