<?php

use App\Exceptions\OrderAuthorizationException;
use App\Exceptions\OrderException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\OrderStatusException;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias untuk middleware admin
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ── OrderNotFoundException → 404 ──────────────────────────────────────
        $exceptions->render(function (OrderNotFoundException $e, Request $request) {
            Log::channel('order_logs')->warning('Order not found', $e->getContext());

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Order tidak ditemukan.'], 404);
            }

            abort(404, 'Order tidak ditemukan.');
        });

        // ── OrderAuthorizationException → 403 ─────────────────────────────────
        $exceptions->render(function (OrderAuthorizationException $e, Request $request) {
            Log::channel('order_logs')->warning('Unauthorized order access', $e->getContext());

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            abort(403, 'Anda tidak memiliki akses ke order ini.');
        });

        // ── OrderStatusException → 422 (Unprocessable) ────────────────────────
        $exceptions->render(function (OrderStatusException $e, Request $request) {
            Log::channel('order_logs')->warning('Invalid order status transition', $e->getContext());

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['status' => $e->getMessage()]);
        });

        // ── OrderException (base) → 500, pesan generik ────────────────────────
        $exceptions->render(function (OrderException $e, Request $request) {
            Log::channel('order_logs')->error('Order operation failed', array_merge(
                $e->getContext(),
                ['error' => $e->getMessage()],
            ));

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Terjadi kesalahan pada pemrosesan order. Silakan coba lagi.'], 500);
            }

            return back()->withErrors(['order' => 'Terjadi kesalahan pada pemrosesan order. Silakan coba lagi.']);
        });

    })->create();
