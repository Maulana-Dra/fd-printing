<?php

use App\Http\Controllers\Api\PricingController;
use Illuminate\Support\Facades\Route;

// ── Pricing API ───────────────────────────────────────────────────────────────
// Digunakan oleh Alpine.js di halaman detail produk untuk kalkulasi harga live.
// Tidak memerlukan autentikasi — hanya read-only kalkulasi.
Route::post('/pricing/calculate', [PricingController::class, 'calculate'])
    ->name('api.pricing.calculate');
