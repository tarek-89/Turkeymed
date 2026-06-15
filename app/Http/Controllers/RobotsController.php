<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Dynamic robots.txt.
     *
     * While the site is not indexable (staging / pre-launch) every crawler is
     * disallowed site-wide. Once indexing is switched on it allows crawling,
     * keeps the admin panel out of the index and advertises the sitemap on the
     * current host — so the URL is always correct without hardcoding a domain.
     */
    public function index(): Response
    {
        if (! config('site.indexable', true)) {
            $body = "User-agent: *\nDisallow: /\n";
        } else {
            $body = implode("\n", [
                'User-agent: *',
                'Disallow: /admin',
                '',
                'Sitemap: '.url('/sitemap.xml'),
                '',
            ]);
        }

        return response($body)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
