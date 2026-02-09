<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateLocale
{
    /**
     * Handle an incoming request.
     * Validates that the locale parameter is valid, if not redirects to default locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        $supportedLocales = config('app.supported_locales', ['ar', 'en', 'nl']);

        // If locale is not valid, redirect to default locale
        if (!$locale || !in_array($locale, $supportedLocales)) {
            $defaultLocale = config('app.locale', 'ar');
            $path = $request->path();
            // Remove the invalid locale from the path
            $pathSegments = explode('/', trim($path, '/'));
            array_shift($pathSegments); // Remove the invalid locale
            $newPath = implode('/', $pathSegments);

            return redirect('/' . $defaultLocale . ($newPath ? '/' . $newPath : ''));
        }

        // Set the locale
        app()->setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
