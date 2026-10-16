<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  string  ...$roles  Allowed roles, or 'any-staff' for owner|admin|order_manager.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            if ($user && ! $user->is_active) {
                // @phpstan-ignore-line Laravel facade magic method
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('admin.login')->with('error', 'Please log in to continue.');
        }

        $allowed = $roles === ['any-staff']
            ? ['owner', 'admin', 'order_manager']
            : $roles;

        if (! in_array($user->role, $allowed, true)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
