<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     * Reads locale from URL (route parameter) only; falls back to session then config.
     * No query string (?locale=) to prevent broken routes and 500 errors.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', ['nl', 'ar', 'en']);
        $routeLocale = $request->route('locale');
        $sessionLocale = session('locale');

        if ($routeLocale && in_array($routeLocale, $supportedLocales, true)) {
            $locale = $routeLocale;
        } elseif ($sessionLocale && in_array($sessionLocale, $supportedLocales, true)) {
            $locale = $sessionLocale;
        } else {
            $locale = config('app.locale', 'nl');
        }

        app()->setLocale($locale);
        session(['locale' => $locale]);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
