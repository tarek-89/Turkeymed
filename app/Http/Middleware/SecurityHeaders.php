<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Attach baseline HTTP security headers to every web response.
     *
     * The CSP deliberately omits script-src/style-src: Filament, Alpine and
     * Livewire rely on inline scripts and eval-style evaluation, and a strict
     * policy without per-request nonces would break the admin panel. The
     * directives set here add clickjacking, base-tag, plugin and mixed-content
     * protections without breaking functionality, plus a frame-src allow-list
     * scoped to the only embed origins the site uses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip non-HTML responses (file downloads, JSON APIs, etc.).
        $contentType = (string) $response->headers->get('Content-Type');
        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $csp = implode('; ', [
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "frame-src 'self' https://www.youtube-nocookie.com https://www.youtube.com https://www.instagram.com https://www.google.com https://maps.google.com",
            'upgrade-insecure-requests',
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
