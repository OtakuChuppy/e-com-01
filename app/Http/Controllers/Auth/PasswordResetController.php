<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    // Ei limiter-ti forgot-password endpoint-ti brute-force ebong email-flooding
    // theke rokkha kore. Ekti IP theke 5 minute-e shorbochcho 5 bar reset link
    // chawa jay. Account enumeration rodh korte response-ti shob-somoy ek rokom thake.
    private const RESET_RATE_LIMIT_KEY_PREFIX = 'password.reset:';

    private const RESET_MAX_ATTEMPTS = 5;

    private const RESET_DECAY_SECONDS = 300;

    public function showLinkForm(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:190']]);

        $key = self::RESET_RATE_LIMIT_KEY_PREFIX.strtolower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::RESET_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->with('error', "Reset link request rate-limited. Please try again in {$seconds} seconds.");
        }

        RateLimiter::hit($key, self::RESET_DECAY_SECONDS);

        // Always respond identically so attackers cannot enumerate accounts.
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'If an account exists for that email, a reset link has been sent. Check server mail logs when MAIL_MAILER=log.');
        }

        return back()->with('success', 'If an account exists for that email, a reset link has been sent.');
    }

    public function showResetForm(string $token): View
    {
        return view('admin.auth.reset-password', ['token' => $token, 'email' => request('email')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:10', 'max:72', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = $password;
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')->with('success', 'Password updated. Please log in.');
        }

        throw ValidationException::withMessages(['email' => 'This reset link is invalid or has expired.']);
    }
}
