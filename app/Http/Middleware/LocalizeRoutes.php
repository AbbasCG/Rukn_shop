<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizeRoutes
{
    /**
     * Handle an incoming request.
     * Extracts the locale from the route parameter and sets it globally.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from route parameter (e.g., {locale} in URL)
        $locale = $request->route('locale');
        $supportedLocales = config('app.supported_locales', ['ar', 'en', 'nl']);

        // Validate locale is supported
        if ($locale && in_array($locale, $supportedLocales)) {
            // Set the application locale
            app()->setLocale($locale);

            // Store in session for persistence
            session(['locale' => $locale]);
        } else {
            // Fallback to session or app default
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
