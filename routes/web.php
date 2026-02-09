<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\PagesController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Redirect legacy query parameter URLs to new locale prefix format
Route::get('/', function () {
    // Default to Arabic if no locale is set
    return redirect('/ar');
})->name('root');

// Legacy redirect: /?locale=en -> /en, etc.
Route::get('/legacy-locale-redirect', function (\Illuminate\Http\Request $request) {
    $locale = $request->query('locale');
    $supportedLocales = config('app.supported_locales', ['ar', 'en', 'nl']);

    if ($locale && in_array($locale, $supportedLocales)) {
        // Get the current path without query parameters
        $path = $request->path();
        return redirect('/' . $locale . ($path !== '/' ? '/' . ltrim($path, '/') : ''));
    }

    // Default to Arabic if no valid locale provided
    return redirect('/ar');
});

// Localized Routes Group
Route::prefix('{locale}')
    ->middleware(['locale.validate'])
    ->group(function () {

        // Home page
        Route::get('/', function () {
            $featuredProducts = Product::where('is_active', true)
                ->with('category')
                ->latest()
                ->take(8)
                ->get();

            return view('home', compact('featuredProducts'));
        })->name('home');

        // Auth-protected routes
        Route::middleware('auth')->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

            // Customer Orders
            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

            // Cart routes (auth-only actions)
            Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
            Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
            Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
            Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

            // Checkout routes
            Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
            Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

            // Order confirmation
            Route::get('/orders/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');
        });

        // Public cart add route and public cart page for guests
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

        // Public Product routes (browse and view)
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

        // Admin-only CRUD routes
        Route::middleware(['auth', 'isAdmin'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            // Product Management
            Route::resource('products', AdminProductController::class);

            // Category Management
            Route::resource('categories', AdminCategoryController::class);

            // Order Management
            Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);

            // User Management
            Route::resource('users', AdminUserController::class);

            // Future modules: coupons, settings, reports
        });

        // Static pages
        Route::get('/about', [PagesController::class, 'about'])->name('about');
        Route::get('/contact', [PagesController::class, 'contact'])->name('contact');
        Route::get('/faq', function () {
            return view('faq');
        })->name('faq');

        // Legal pages
        Route::get('/privacy-policy', function () {
            return view('legal.privacy-policy');
        })->name('privacy-policy');
        Route::get('/terms-of-service', function () {
            return view('legal.terms-of-service');
        })->name('terms-of-service');
    });

require __DIR__ . '/auth.php';
