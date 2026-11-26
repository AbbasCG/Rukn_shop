<?php

if (!function_exists('r')) {
    /**
     * Generate a URL to a named route with automatic locale parameter injection.
     * This is wrapper around Laravel's route() helper that auto-injects the current locale.
     * Use this instead of route() in blade templates: {{ r('products.index') }}
     * 
     * @param string $name The route name
     * @param mixed $parameters Optional route parameters
     * @param bool $absolute Whether to generate absolute URL
     * @return string The generated route URL
     */
    function r($name, $parameters = [], $absolute = true)
    {
        // Ensure parameters is an array
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        // Auto-inject locale if not provided and the route needs it
        try {
            $router = \Illuminate\Support\Facades\Route::getRoutes();
            $route = $router->getByName($name);

            if ($route && in_array('locale', $route->parameterNames()) && !isset($parameters['locale'])) {
                $parameters['locale'] = app()->getLocale();
            }
        } catch (\Throwable $e) {
            // Route not found, let Laravel handle the error
        }

        // Use Laravel's URL::route() to generate the URL
        return \Illuminate\Support\Facades\URL::route($name, $parameters, $absolute);
    }
}

if (!function_exists('localizePath')) {
    /**
     * Generate a URL for the given route with the specified locale.
     * Preserves the current path while replacing the locale prefix.
     * 
     * @param string $locale The target locale (en, nl, ar)
     * @param string|null $path Optional path to use instead of current request path
     * @return string The localized URL
     */
    function localizePath($locale, $path = null)
    {
        if ($path === null) {
            $path = request()->path();
        }

        // Remove the current locale prefix from the path if it exists
        $supportedLocales = config('app.supported_locales', ['nl', 'ar', 'en']);
        $currentPath = trim($path, '/');

        foreach ($supportedLocales as $existingLocale) {
            if (strpos($currentPath, $existingLocale . '/') === 0) {
                // Remove the locale prefix
                $currentPath = substr($currentPath, strlen($existingLocale) + 1);
                break;
            }
            if ($currentPath === $existingLocale) {
                // Remove the locale if it's the only segment
                $currentPath = '';
                break;
            }
        }

        // Build the new path with the new locale
        $newPath = '/' . $locale;
        if (!empty($currentPath)) {
            $newPath .= '/' . $currentPath;
        }

        return url($newPath);
    }
}

if (!function_exists('isCurrentLocale')) {
    /**
     * Check if the given locale is the current application locale.
     * 
     * @param string $locale The locale to check
     * @return bool True if the locale matches the current application locale
     */
    function isCurrentLocale($locale)
    {
        return app()->getLocale() === $locale;
    }
}

if (!function_exists('getActiveLocale')) {
    /**
     * Get the currently active locale.
     * 
     * @return string The current locale
     */
    function getActiveLocale()
    {
        return app()->getLocale();
    }
}

if (!function_exists('money')) {
    /**
     * Format an amount as a price for the active locale, always with Western
     * digits: nl "€ 1.249,50", en "€1,249.50", ar "1,249.50 €".
     */
    function money($amount, string $currency = 'EUR'): string
    {
        $amount = (float) $amount;
        $locale = match (app()->getLocale()) {
            'nl' => 'nl_NL',
            'ar' => 'ar@numbers=latn',
            default => 'en_GB',
        };

        if (class_exists(\NumberFormatter::class)) {
            $formatted = (new \NumberFormatter($locale, \NumberFormatter::CURRENCY))->formatCurrency($amount, $currency);
            if ($formatted !== false) {
                // Drop the leading RLM that ICU adds for Arabic; the page direction handles it.
                return str_replace("\u{200F}", '', $formatted);
            }
        }

        return '€' . number_format($amount, 2, ',', '.');
    }
}

if (!function_exists('policy_text')) {
    /**
     * Translate a shipping/returns text, filling in the store policy values
     * from config/shop.php (:threshold, :rate, :min_days, :max_days, :return_days).
     */
    function policy_text(string $key): string
    {
        return __($key, \App\Support\Shipping::textParams());
    }
}
