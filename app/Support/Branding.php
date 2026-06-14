<?php

namespace App\Support;

use App\Models\Setting;

class Branding
{
    /**
     * The site-wide default Open Graph / Twitter share image: the admin-uploaded
     * one if set, otherwise the static branded asset shipped in /public.
     */
    public static function ogImageUrl(): string
    {
        $path = Setting::get('org.og_image');

        if (is_string($path) && $path !== '') {
            return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');
        }

        return asset(ltrim((string) config('site.og_image'), '/'));
    }
}
