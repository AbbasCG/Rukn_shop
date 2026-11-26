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
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Visiting '/' must redirect to default locale (NL)
Route::get('/', function () {
    return redirect('/nl');
})->name('root');

// Localized routes: prefix /{locale}, allowed nl, ar, en
Route::prefix('{locale}')
    ->where(['locale' => 'nl|en|ar'])
    ->middleware([\App\Http\Middleware\SetLocale::class])
    ->group(function () {

        // Home page
        Route::get('/', function () {
            $newArrivals = Product::where('is_active', true)
                ->with(['category', 'images'])
                ->latest()
                ->take(4)
                ->get();

            // Real bestsellers (most units ordered), never repeating new arrivals.
            // The section is hidden until there are enough sales to fill a row.
            $bestsellers = Product::where('is_active', true)
                ->whereNotIn('id', $newArrivals->pluck('id'))
                ->whereHas('orderItems')
                ->withSum('orderItems as units_sold', 'quantity')
                ->with(['category', 'images'])
                ->orderByDesc('units_sold')
                ->take(4)
                ->get();

            return view('home', compact('newArrivals', 'bestsellers'));
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
        Route::get('/categories/{category:slug}', [CategoryController::class, 'publicShow'])->name('categories.show');

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

            // Contact form messages
            Route::resource('messages', AdminContactMessageController::class)->only(['index', 'update', 'destroy']);

            // Future modules: coupons, settings, reports
        });

        // Static pages
        Route::get('/about', [PagesController::class, 'about'])->name('about');
        Route::get('/contact', [PagesController::class, 'contact'])->name('contact');
        // At most 5 messages per minute per visitor (spam protection, with the honeypot).
        Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
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

        require __DIR__ . '/auth.php';
    });
