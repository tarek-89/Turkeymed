<?php

use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Cloudflare (reverse proxy): trust forwarded headers so the
        // app sees real visitor IPs and the correct https scheme/host. Lock
        // the origin firewall to Cloudflare IPs to prevent header spoofing.
        $middleware->trustProxies(at: '*');

        // Global: resolve old-WordPress-URL redirects before routing, so it also
        // catches paths that match no route and 404s.
        $middleware->prepend(HandleRedirects::class);

        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
