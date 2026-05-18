<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: pastikan hanya admin (is_admin = true) yang bisa akses route /admin.
 * Filament sudah meng-handle ini via FilamentUser::canAccessPanel(),
 * tapi middleware ini menjadi lapisan tambahan di level route.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk administrator.');
        }

        return $next($request);
    }
}
