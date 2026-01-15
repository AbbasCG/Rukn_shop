<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from query parameter, session, or config default
        $locale = $request->query('locale');

        if ($locale && in_array($locale, config('app.supported_locales', ['en', 'ar', 'nl']))) {
            // Set the application locale
            app()->setLocale($locale);
            
            // Store in session for persistence
            session(['locale' => $locale]);
        } else {
            // Use session locale if available
            $sessionLocale = session('locale');
            if ($sessionLocale && in_array($sessionLocale, config('app.supported_locales', ['en', 'ar', 'nl']))) {
                app()->setLocale($sessionLocale);
            } else {
                // Use browser language or app default
                $appDefault = config('app.locale', 'en');
                app()->setLocale($appDefault);
            }
        }

        return $next($request);
    }
}
