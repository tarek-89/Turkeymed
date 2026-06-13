<?php

namespace App\Support;

class Url
{
    /**
     * Neutralise dangerous URL schemes in admin-supplied free-text links.
     *
     * Relative paths ("/category/…", "#anchor") and http(s) URLs pass through;
     * anything with a scheme other than http/https (javascript:, data:,
     * vbscript:, …) is replaced with "#" so it can't execute on click.
     */
    public static function safe(?string $url, string $fallback = '#'): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return $fallback;
        }

        // No scheme (relative path, anchor, protocol-relative is treated as relative-safe enough).
        if (! preg_match('~^([a-z][a-z0-9+.\-]*):~i', $url, $m)) {
            return $url;
        }

        return in_array(strtolower($m[1]), ['http', 'https'], true) ? $url : $fallback;
    }
}
