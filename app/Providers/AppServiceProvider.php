<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register a Blade directive for routes that automatically includes locale
        Blade::directive('route', function ($expression) {
            // Use our r() helper which auto-injects locale
            return "<?php echo r({$expression}); ?>";
        });
    }
}
