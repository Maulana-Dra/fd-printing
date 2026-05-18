<?php

namespace App\Providers;

use App\Models\Order;
use App\Policies\OrderPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // ── Policies ──────────────────────────────────────────────────────────
        Gate::policy(Order::class, OrderPolicy::class);

        // ── Rate Limiters ─────────────────────────────────────────────────────

        // Checkout: max 10 submit per 5 menit per user (anti spam order)
        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinutes(5, 10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn () => back()
                    ->withErrors(['rate' => 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.'])
                );
        });

        // Konfirmasi pembayaran: max 5 upload per 5 menit per user
        RateLimiter::for('payment-confirm', function (Request $request) {
            return Limit::perMinutes(5, 5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn () => back()
                    ->withErrors(['rate' => 'Terlalu banyak percobaan konfirmasi. Tunggu beberapa menit.'])
                );
        });

        // Upload file desain (cart): max 10 upload per menit per user
        RateLimiter::for('upload-design', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip());
        });

        // ── Force HTTPS di production ──────────────────────────────────────────
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
