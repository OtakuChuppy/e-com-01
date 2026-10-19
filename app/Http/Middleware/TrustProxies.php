<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trust reverse-proxy IPs so $request->ip() returns the real client IP
 * instead of the proxy's IP. This keeps IP-based rate limiting (login,
 * password reset, cart, checkout) effective behind load balancers.
 *
 * Set TRUSTED_PROXIES in .env (comma-separated CIDRs or '*'). In production
 * on Laravel Cloud / managed hosting, leave empty or set the platform CIDR.
 */
class TrustProxies
{
    public function handle(Request $request, Closure $next): Response
    {
        $proxies = trim((string) config('app.trusted_proxies', ''));

        if ($proxies !== '') {
            $request->setTrustedProxies(
                is_string($proxies) ? explode(',', $proxies) : [],
                Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PORT
            );
        }

        return $next($request);
    }
}
