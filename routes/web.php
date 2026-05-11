<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// ── Katalog & Detail Produk ───────────────────────────────────────────────────
Route::get('/k/{slug?}', [ProductController::class, 'index'])->name('products.index');
Route::get('/p/{slug}',  [ProductController::class, 'show'])->name('products.show');

// ── Keranjang (guest + auth) ──────────────────────────────────────────────────
Route::get('/cart',            [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add',       [CartController::class, 'add'])->name('cart.add');
Route::match(['POST','PATCH'], '/cart/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{id}',    [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart',         [CartController::class, 'clear'])->name('cart.clear');

// ── Auth Area ─────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Checkout
    Route::get('/checkout',  [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Orders
    Route::get('/pesanan',              fn () => view('dashboard'))->name('orders.index');
    Route::get('/pesanan/{order}',      fn ($order) => abort(404))->name('orders.show');
    Route::get('/pesanan/{order}/bayar', fn ($order) => abort(404))->name('orders.payment');
});

require __DIR__.'/auth.php';
