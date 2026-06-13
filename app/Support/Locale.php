<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;

class Locale
{
    public const DEFAULT = 'en';

    /**
     * All supported locale codes (e.g. ['en', 'fr', 'es', 'ar']).
     *
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(config('locales.supported', []));
    }

    public static function isSupported(string $code): bool
    {
        return array_key_exists($code, config('locales.supported', []));
    }

    /** The text direction ('ltr' or 'rtl') for a locale. */
    public static function direction(?string $code = null): string
    {
        $code ??= app()->getLocale();

        return config("locales.supported.{$code}.dir", 'ltr');
    }

    /** The language's own name (e.g. 'Français'). */
    public static function native(string $code): string
    {
        return config("locales.supported.{$code}.native", strtoupper($code));
    }

    /**
     * Build the URL for the current request in a different locale, preserving
     * the path and query string. English (the default) is served at the root
     * with no prefix; other locales are prefixed with /{code}.
     */
    public static function switchUrl(string $target, ?Request $request = null): string
    {
        $request ??= RequestFacade::instance();

        $segments = array_values(array_filter(explode('/', $request->path())));

        // Strip an existing locale prefix so we're left with the "bare" path.
        if (isset($segments[0]) && self::isSupported($segments[0])) {
            array_shift($segments);
        }

        $bare = implode('/', $segments);

        $path = $target === self::DEFAULT
            ? '/'.$bare
            : '/'.$target.($bare !== '' ? '/'.$bare : '');

        $path = '/'.ltrim($path, '/');

        $query = $request->getQueryString();

        return url($path).($query ? '?'.$query : '');
    }

    /**
     * The locale options for a language switcher: each supported locale with
     * its code, native name, target URL and whether it is currently active.
     *
     * When $alternates is given (content pages: language code => translated
     * URL), each locale links to its actual translation — translated slugs
     * differ per language, so a naive prefix swap would 404. Locales without
     * a translation are omitted. Without $alternates (home, categories, ...)
     * the path-preserving switchUrl() is used.
     *
     * @param  array<string, string>|null  $alternates
     * @return list<array{code: string, native: string, url: string, active: bool}>
     */
    public static function options(?array $alternates = null, ?Request $request = null): array
    {
        $current = app()->getLocale();

        $options = array_map(static function (string $code) use ($current, $alternates, $request): ?array {
            if ($alternates !== null && ! isset($alternates[$code])) {
                return null;
            }

            return [
                'code' => $code,
                'native' => self::native($code),
                'url' => $alternates[$code] ?? self::switchUrl($code, $request),
                'active' => $code === $current,
            ];
        }, self::codes());

        return array_values(array_filter($options));
    }
}
