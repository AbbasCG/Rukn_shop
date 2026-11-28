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

        // Favourites saved as a guest move into the account on every login
        // (password, two-step code, registration).
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            if ($event->user instanceof \App\Models\User) {
                \App\Support\Wishlist::mergeGuestInto($event->user);
            }
        });
    }
}
