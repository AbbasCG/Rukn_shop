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
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GuestOrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Mollie calls this when a payment changes (no locale, no CSRF: see bootstrap/app.php).
Route::post('/payments/mollie/webhook', [PaymentController::class, 'webhook'])
    ->middleware('throttle:60,1')->name('payments.webhook');

// Search engines: sitemap with every page in every language, and robots rules.
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

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
                ->with(['category', 'images', 'variants'])
                ->latest()
                ->take(4)
                ->get();

            // Real bestsellers (most units ordered), never repeating new arrivals.
            // The section is hidden until there are enough sales to fill a row.
            $bestsellers = Product::where('is_active', true)
                ->whereNotIn('id', $newArrivals->pluck('id'))
                ->whereHas('orderItems.order', fn ($order) => $order->where('status', '!=', 'cancelled'))
                ->with(['category', 'images', 'variants'])
                ->orderByUnitsSold()
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
            Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');


            // Checkout routes

            // Order confirmation
            Route::get('/orders/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');
        });

        // Checkout: logged-in customers and guests.
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        // Online payment: back from Mollie, and trying again after a failed payment.
        Route::get('/payment/return/{order}/{token}', [PaymentController::class, 'return'])
            ->whereNumber('order')->where('token', '[A-Za-z0-9]{40}')->name('payments.return');
        Route::post('/payment/retry/{order}/{token}', [PaymentController::class, 'retry'])
            ->whereNumber('order')->where('token', '[A-Za-z0-9]{40}')->middleware('throttle:10,1')->name('payments.retry');

        // Orders without an account: private link + "find my order".
        Route::get('/orders/view/{order}/{token}', [GuestOrderController::class, 'show'])
            ->whereNumber('order')->where('token', '[A-Za-z0-9]{40}')->name('orders.view');
        Route::get('/orders/placed/{order}/{token}', [GuestOrderController::class, 'confirmation'])
            ->whereNumber('order')->where('token', '[A-Za-z0-9]{40}')->name('orders.placed');
        Route::get('/orders/lookup', [GuestOrderController::class, 'lookupForm'])->name('orders.lookup');

        // Invoice PDF, from the private order link.
        Route::get('/orders/invoice/{order}/{token}', [\App\Http\Controllers\InvoiceController::class, 'show'])
            ->whereNumber('order')->where('token', '[A-Za-z0-9]{40}')->middleware('throttle:30,1')->name('invoices.show');

        // Reviews by buyers, from the private order link.
        Route::get('/orders/review/{order}/{token}', [\App\Http\Controllers\ReviewController::class, 'create'])
            ->whereNumber('order')->where('token', '[A-Za-z0-9]{40}')->name('reviews.create');
        Route::post('/orders/review/{order}/{token}', [\App\Http\Controllers\ReviewController::class, 'store'])
            ->whereNumber('order')->where('token', '[A-Za-z0-9]{40}')->middleware('throttle:10,1')->name('reviews.store');
        Route::post('/orders/lookup', [GuestOrderController::class, 'lookup'])
            ->middleware('throttle:5,1')->name('orders.lookup.submit');

        // Cart: for guests (session) and logged-in customers (saved). A line is
        // a saved cart id, or for guests "<productId>" / "<productId>:<variantId>".
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::patch('/cart/{line}', [CartController::class, 'update'])->where('line', '[0-9]+(:[0-9]+)?')->name('cart.update');
        Route::delete('/cart/{line}', [CartController::class, 'destroy'])->where('line', '[0-9]+(:[0-9]+)?')->name('cart.destroy');
        Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
        Route::post('/cart/coupon', [\App\Http\Controllers\CouponController::class, 'store'])->middleware('throttle:10,1')->name('cart.coupon.store');
        Route::delete('/cart/coupon', [\App\Http\Controllers\CouponController::class, 'destroy'])->name('cart.coupon.destroy');
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
            Route::get('/orders/export', [\App\Http\Controllers\Admin\OrderExportController::class, 'download'])->name('orders.export');
            Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
            Route::get('/orders/{order}/invoice', [\App\Http\Controllers\InvoiceController::class, 'admin'])->whereNumber('order')->name('orders.invoice');

            // User Management
            Route::resource('users', AdminUserController::class);

            // Contact form messages
            Route::resource('messages', AdminContactMessageController::class)->only(['index', 'update', 'destroy']);

            // Discount codes
            Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->except(['show', 'create']);

            // Review moderation
            Route::resource('reviews', \App\Http\Controllers\Admin\ReviewController::class)->only(['index', 'update', 'destroy']);

            // Emails that failed in the background
            Route::post('/mail-queue/retry', [\App\Http\Controllers\Admin\MailQueueController::class, 'retry'])->name('mail-queue.retry');
            Route::post('/mail-queue/discard', [\App\Http\Controllers\Admin\MailQueueController::class, 'discard'])->name('mail-queue.discard');

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
        Route::view('/cookies', 'legal.cookies')->name('cookies');
        Route::view('/withdrawal-form', 'legal.withdrawal')->name('withdrawal-form');

        require __DIR__ . '/auth.php';
    });
