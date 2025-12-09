<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PagesController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $featuredProducts = Product::where('is_active', true)
        ->with('category')
        ->latest()
        ->take(8)
        ->get();
    
    return view('home', compact('featuredProducts'));
})->name('home');

// Route::get('/', [PagesController::class, 'home'])->name('home');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Cart routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.add');
});

// Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// Static pages
Route::view('/over-ons', [PagesController::class, 'about'])->name('about');
Route::view('/contact', [PagesController::class, 'contact'])->name('contact');

require __DIR__.'/auth.php';
                        