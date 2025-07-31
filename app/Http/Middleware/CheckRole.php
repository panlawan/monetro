<?php

// app/Http/Middleware/CheckRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (! $user->is_active) {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account disabled'], 403);
            }

            return redirect()->route('login')->with('error', 'บัญชีของคุณถูกปิดใช้งาน');
        }

        // Check roles
        if (! $user->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Insufficient permissions'], 403);
            }
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return $next($request);
    }
}
