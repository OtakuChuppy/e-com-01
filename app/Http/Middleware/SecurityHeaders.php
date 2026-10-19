<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add baseline hardening headers to every web response.
     *
     * Banglish: ei middleware-ti protiti response-e security header jog kore ta je website-ti
     * aro nirapod hoye uthe. Clickjacking, MIME sniffing, referrer leak, ebong
     * camera/mic/geolocation access block korte Permissions-Policy pathano hoye ache.
     *
     * Production-e HSTS jog kora hoyeche jar madhyome browser-ti shudhumatro HTTPS
     * byabohar kore site-ti access kore. Baki header-gulo holo baseline hardening.
     *
     * TODO (planned, not done yet): kora Content-Security-Policy (CSP).
     * Storefront-ti alpine.js, tailwind CDN ebong per-page inline script byabohar kore,
     * tai ektu strict CSP dile site-ti bhangte pare. Agami feature hishebe non-inline
     * script-e rupantor kore full CSP enabe korar porikolpona ache.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Legacy XSS filter header — purono browser-gulor jonno otirikto ekti dorja bondho kore fele.
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Production-e HSTS: browser-ke bolte hoy je shudhumatro HTTPS-e site-ti visit korte hobe.
        // 1 bochor (31536000 second) ebong subdomain-gulor jonnoo applcable (includeSubDomains).
        if (app()->environment('production') && $request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
