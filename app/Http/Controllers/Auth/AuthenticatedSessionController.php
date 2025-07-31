<?php

// app/Http/Controllers/Auth/AuthenticatedSessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Check if user is active before authentication
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && ! $user->is_active) {
            return back()->withErrors([
                'email' => 'บัญชีของคุณถูกปิดใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            ])->onlyInput('email');
        }

        $request->authenticate();

        $request->session()->regenerate();

        // อัปเดต last_login_at - เพิ่มบรรทัดนี้
        Auth::user()->update(['last_login_at' => now()]);

        // Redirect based on user role
        return $this->redirectBasedOnRole(Auth::user());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->hasRole('moderator')) {
            return redirect()->intended(route('moderator.dashboard'));
        } else {
            return redirect()->intended(route('dashboard'));
        }
    }
}
