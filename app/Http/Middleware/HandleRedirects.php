<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    /**
     * Path prefixes that must never be touched: the admin panel, framework
     * internals and asset routes. Everything else is public content.
     *
     * @var list<string>
     */
    private const SKIP_PREFIXES = [
        'admin',
        'livewire',
        'build',
        'storage',
        'vendor',
        'up',
        'health',
    ];

    /**
     * Send old WordPress URLs to their new home before routing runs.
     *
     * Registered as a global middleware so it also catches paths that match no
     * route (multi-segment WP URLs) and 404s, not just resolved routes. Two
     * layers:
     *   1. An explicit `redirects` table entry (genuine slug changes).
     *   2. Trailing-slash normalisation — WordPress served every URL with a
     *      trailing slash; the migrated slugs are identical, so a single 301
     *      from "/slug/" to "/slug" recovers the bulk of the old link equity.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $pathInfo = $request->getPathInfo();        // leading slash, raw (percent-encoded)
        $key = trim($pathInfo, '/');

        if ($key === '' || $this->shouldSkip($key)) {
            return $next($request);
        }

        if ($redirect = Redirect::match($key)) {
            $redirect->recordHit();

            return redirect()->to($this->withQuery($redirect->target(), $request), $redirect->status_code);
        }

        if (str_ends_with($pathInfo, '/')) {
            return redirect()->to($this->withQuery('/'.$key, $request), 301);
        }

        return $next($request);
    }

    private function shouldSkip(string $key): bool
    {
        foreach (self::SKIP_PREFIXES as $prefix) {
            if ($key === $prefix || str_starts_with($key, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Preserve the original query string unless the target already carries one.
     */
    private function withQuery(string $target, Request $request): string
    {
        $query = $request->getQueryString();

        if ($query === null || $query === '' || str_contains($target, '?')) {
            return $target;
        }

        return $target.'?'.$query;
    }
}
