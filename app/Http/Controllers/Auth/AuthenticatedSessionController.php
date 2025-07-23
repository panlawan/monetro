<?php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Track login
        if (auth()->user()) {
            auth()->user()->recordLogin();
        }

        // Redirect based on user role
        return $this->redirectUser();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectUser(): RedirectResponse
    {
        $user = auth()->user();

        // Check if user can access admin panel
        if ($user && $user->canAccessAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Regular users go to normal dashboard
        return redirect()->intended(RouteServiceProvider::HOME);
    }
}