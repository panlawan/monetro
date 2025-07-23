<?php
// app/Http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isActive()) {
            auth()->logout();
            return redirect()->route('login')
                           ->with('error', 'Your account has been deactivated.');
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Access denied. Required role: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}