<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Load our localization helper before the application boots
require_once dirname(__DIR__) . '/app/Helpers/LocalizationHelper.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render (and similar hosts) terminate HTTPS at their proxy and forward
        // over HTTP. Trusting it makes Laravel see the real scheme and client IP,
        // so generated URLs (canonical, sitemap, og:url, emails) use https and
        // rate limits apply per visitor instead of per proxy.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'isAdmin' => \App\Http\Middleware\IsAdmin::class,
            'locale.validate' => \App\Http\Middleware\ValidateLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
