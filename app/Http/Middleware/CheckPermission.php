<?php

// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
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

        // Check permission
        if (! $user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Permission denied'], 403);
            }
            abort(403, 'คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        return $next($request);
    }
}
