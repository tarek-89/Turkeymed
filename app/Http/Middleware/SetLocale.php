<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Resolve the active locale from the URL and apply it to the application,
     * sharing the locale and text direction with all views.
     *
     * The URL is the single source of truth: English (the default) is served
     * unprefixed at the root, every other locale lives under /{code}/. This
     * keeps each piece of content on exactly one canonical URL, which is what
     * search engines and the page's hreflang tags expect.
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->resolve($request));

        View::share('currentLocale', app()->getLocale());
        View::share('textDirection', Locale::direction());

        return $next($request);
    }

    /**
     * Resolve the locale from the matched {locale} route parameter, then the
     * first URL segment, falling back to the application default.
     */
    private function resolve(Request $request): string
    {
        $routeLocale = $request->route('locale');
        if (is_string($routeLocale) && Locale::isSupported($routeLocale)) {
            return $routeLocale;
        }

        $segment = explode('/', trim($request->path(), '/'))[0] ?? '';
        if (Locale::isSupported($segment)) {
            return $segment;
        }

        return config('app.locale', Locale::DEFAULT);
    }
}
