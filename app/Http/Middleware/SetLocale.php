<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     * This middleware is kept for backward compatibility.
     * LocalizeRoutes middleware now handles locale setting from route parameter.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // The locale should already be set by LocalizeRoutes middleware
        // This serves as a fallback for routes not under locale prefix
        $locale = app()->getLocale();
        $supportedLocales = config('app.supported_locales', ['ar', 'en', 'nl']);

        if (!in_array($locale, $supportedLocales)) {
            $sessionLocale = session('locale');
            if ($sessionLocale && in_array($sessionLocale, $supportedLocales)) {
                app()->setLocale($sessionLocale);
            } else {
                $appDefault = config('app.locale', 'ar');
                app()->setLocale($appDefault);
            }
        }

        return $next($request);
    }
}
