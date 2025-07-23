<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')
                           ->with('error', 'Please login to access this area.');
        }

        // Check if user is active
        if (!$user->isActive()) {
            auth()->logout();
            return redirect()->route('login')
                           ->with('error', 'Your account has been deactivated.');
        }

        // Check if email is verified
        if (!$user->email_verified_at) {
            return redirect()->route('verification.notice')
                           ->with('error', 'Please verify your email address.');
        }

        // Check role permissions
        $hasPermission = match($role) {
            'super_admin' => $user->isSuperAdmin(),
            'admin' => $user->isAdmin(),
            'moderator' => $user->isModerator(),
            default => $user->canAccessAdmin()
        };

        if (!$hasPermission) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}