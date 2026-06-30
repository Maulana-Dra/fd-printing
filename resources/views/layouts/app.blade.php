<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? config('printing.company.tagline') }}">
    <meta name="robots" content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:title"       content="{{ $title ?? config('app.name') }}">
    <meta property="og:description" content="{{ $description ?? config('printing.company.tagline') }}">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="{{ url()->current() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('printing.company.name') }}</title>

    {{-- Preconnect fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Stack untuk head tambahan per halaman --}}
    @stack('head')
</head>

<body class="tap-highlight min-h-screen" x-data>

    {{-- ── Toasts ────────────────────────────────────────────────── --}}
    @include('layouts.partials._toast')

    {{-- ── Cart Drawer ───────────────────────────────────────────── --}}
    @include('layouts.partials._cart_drawer')

    {{-- ── Header ───────────────────────────────────────────────── --}}
    @include('layouts.partials._header')

    {{-- ── Flash Messages ────────────────────────────────────────── --}}
    @if (session('success') || session('error') || session('warning') || session('info'))
        <div class="container-app pt-4" x-data>
            @if (session('success'))
                <div class="alert-success mb-3 animate-fade-in">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="alert-danger mb-3 animate-fade-in">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert-warning mb-3 animate-fade-in">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Main Content ──────────────────────────────────────────── --}}
    <main class="page-content">
        {{ $slot }}
    </main>

    {{-- ── Footer (Desktop only) ────────────────────────────────── --}}
    @include('layouts.partials._footer')

    {{-- ── Bottom Navigation (Mobile only) ─────────────────────── --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
        <div class="grid grid-cols-4 h-16">

            {{-- Home --}}
            <a href="{{ url('/') }}" class="bottom-nav-item {{ request()->is('/') ? 'active' : '' }}">
                <svg class="w-6 h-6" fill="{{ request()->is('/') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->is('/') ? '0' : '1.8' }}"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[10px] font-medium">Beranda</span>
            </a>

            {{-- Kategori --}}
            <a href="{{ route('products.index') }}"
                class="bottom-nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <svg class="w-6 h-6" fill="{{ request()->routeIs('products.*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('products.*') ? '0' : '1.8' }}"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span class="text-[10px] font-medium">Kategori</span>
            </a>

            {{-- Transaksi --}}
            <a href="{{ route('orders.index') }}"
                class="bottom-nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <svg class="w-6 h-6" fill="{{ request()->routeIs('orders.*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('orders.*') ? '0' : '1.8' }}"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-[10px] font-medium">Transaksi</span>
            </a>

            {{-- Akun --}}
            <a href="{{ auth()->check() ? route('profile.edit') : route('login') }}"
                class="bottom-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                @auth
                    <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center">
                        <span class="text-[10px] font-bold text-orange-600">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                @else
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                @endauth
                <span class="text-[10px] font-medium">{{ auth()->check() ? 'Akun' : 'Masuk' }}</span>
            </a>
        </div>
    </nav>

    {{-- Sync Alpine Cart Store dengan Laravel Session Cart --}}
    <script>
        document.addEventListener('alpine:init', () => {
            // Bersihkan data local storage lama dari versi sebelumnya
            localStorage.removeItem('fd_cart');
            
            Alpine.store('cart').items = @json(app(App\Services\CartService::class)->getItems()->values());
        });
    </script>

    {{-- Stack untuk scripts tambahan per halaman --}}
    @stack('scripts')

</body>
</html>
