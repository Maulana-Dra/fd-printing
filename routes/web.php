<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DesignFileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
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
Route::post('/cart/add', [CartController::class, 'add'])
    ->middleware('throttle:upload-design')
    ->name('cart.add');
Route::match(['POST','PATCH'], '/cart/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{id}',    [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart',         [CartController::class, 'clear'])->name('cart.clear');

// ── Auth Area ─────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Checkout — rate limit 10 req/menit (mencegah spam order)
    Route::get('/checkout',  [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:checkout')
        ->name('checkout.store');

    // Orders & Akun
    Route::get('/akun/pesanan',              [AccountController::class, 'orders'])->name('orders.index');
    Route::get('/akun/pesanan/{orderNumber}',[AccountController::class, 'orderDetail'])->name('orders.show');

    // Payment — rate limit upload bukti: 5 req/menit per user
    Route::get('/pesanan/{order}/bayar',  [PaymentController::class, 'show'])->name('orders.payment');
    Route::post('/pesanan/{order}/bayar/konfirmasi', [PaymentController::class, 'confirm'])
        ->middleware('throttle:payment-confirm')
        ->name('orders.payment.confirm');
    Route::get('/pesanan/{order}/terima-kasih', [PaymentController::class, 'thankyou'])->name('orders.thankyou');

    // ── Design File Download (signed URL, admin only) ─────────────────────────
    // URL-nya di-generate oleh admin via Filament, berlaku 60 menit
    Route::get('/admin/design-download/{orderItem}', [DesignFileController::class, 'download'])
        ->middleware(['signed', 'throttle:10,1'])
        ->name('design.download');
});

require __DIR__.'/auth.php';
