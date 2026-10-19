<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    // Staff admin panel-er login. Rate-limit kora hoyeche (5 attempt, 1 minute)
    // jar madhyome brute-force attack rodh hoye thake. Session regenerate kora
    // hoy fixation attack theke rokkha-te.

    public function show(Request $request): View|RedirectResponse
    {
        // Login kora thaka user-ke dashboard-e pathano hoyeche.
        if ($request->user()?->is_active) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:72'],
            'remember' => ['nullable', 'boolean'],
        ]);

        // Rate limiter key: email + IP. 5 attempt-e limit.
        $key = 'login:'.strtolower($validated['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $remember = (bool) ($validated['remember'] ?? false);

        // Chesta korun credential match kore ki na; is_active true thaka user shudhumatro login korte parbe.
        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['password'], 'is_active' => true], $remember)) {
            RateLimiter::hit($key, 120);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($key);
        // Session fixation rodh korte session ID notun kore generate kora hoyeche.
        $request->session()->regenerate();

        $user = $request->user();
        if (! in_array($user->role, ['owner', 'admin', 'order_manager', 'marketer'], true)) {
            // @phpstan-ignore-line Laravel facade magic method
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This account does not have staff access.',
            ]);
        }

        return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, '.$user->name.'.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Logout korar sathe session invalidate ebong regenerate kora hoyeche
        // jar madhyome session fixation attack rodh hoye thake.
        // @phpstan-ignore-line Laravel facade magic method
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been logged out.');
    }
}
