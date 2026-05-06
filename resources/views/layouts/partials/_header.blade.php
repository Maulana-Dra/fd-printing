{{--
    Header Utama — FD Printing
    Berisi: logo, search bar, nav kategori, cart icon + badge, account dropdown.
    Alpine stores: $store.ui, $store.cart
--}}
<header
    class="fixed top-0 left-0 right-0 z-40 transition-all duration-300"
    :class="$store.ui.scrolled
        ? 'bg-white/95 backdrop-blur-md shadow-md border-b border-gray-100'
        : 'bg-white border-b border-gray-100'"
>
    <div class="container-app">

        {{-- ── Row 1: Logo + Search + Actions ── --}}
        <div class="flex items-center gap-3 h-16">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center gap-2.5 tap-highlight">
                <div class="w-9 h-9 rounded-xl gradient-orange flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </div>
                <span class="hidden sm:block text-lg font-bold text-gray-900 leading-tight">
                    {{ config('printing.company.name', 'FD Printing') }}
                </span>
            </a>

            {{-- Search Bar (Desktop) --}}
            <form action="{{ route('products.index') }}" method="GET"
                class="hidden md:flex flex-1 max-w-xl mx-auto">
                <div class="search-bar w-full">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari produk percetakan..."
                        class="flex-1 bg-transparent text-sm text-gray-900 placeholder-gray-400 focus:outline-none"
                    >
                    <button type="submit"
                        class="flex-shrink-0 px-3 py-1 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-lg transition-colors duration-150">
                        Cari
                    </button>
                </div>
            </form>

            {{-- Right Actions --}}
            <div class="flex items-center gap-1 ml-auto md:ml-0">

                {{-- Mobile Search Toggle --}}
                <button type="button"
                    @click="$store.ui.searchFocused = !$store.ui.searchFocused"
                    class="md:hidden btn-ghost p-2.5 tap-highlight"
                    aria-label="Buka pencarian">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                {{-- Cart Button --}}
                <button type="button"
                    @click="$store.ui.toggleCart()"
                    class="relative btn-ghost p-2.5 tap-highlight"
                    aria-label="Keranjang belanja">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{-- Badge --}}
                    <span
                        x-show="$store.cart.count > 0"
                        x-text="$store.cart.count > 99 ? '99+' : $store.cart.count"
                        x-cloak
                        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full
                               bg-orange-600 text-white text-[10px] font-bold
                               flex items-center justify-center leading-none animate-scale-in">
                    </span>
                </button>

                {{-- Account Dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button type="button"
                        @click="open = !open"
                        @keydown.escape="open = false"
                        class="btn-ghost p-2.5 tap-highlight"
                        aria-label="Menu akun"
                        :aria-expanded="open">
                        @auth
                            <div class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center overflow-hidden">
                                <span class="text-xs font-bold text-orange-600">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                            </div>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        @endauth
                    </button>

                    {{-- Dropdown --}}
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        @click.outside="open = false"
                        x-cloak
                        class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 animate-fade-in">

                        @auth
                            {{-- User Info --}}
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('dashboard') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    Dashboard
                                </a>
                                <a href="{{ route('orders.index') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Pesanan Saya
                                </a>
                                <a href="{{ route('profile.edit') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profil Saya
                                </a>
                            </div>

                            <div class="border-t border-gray-100 py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="py-1">
                                <a href="{{ route('login') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    Masuk
                                </a>
                                <a href="{{ route('register') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                    Daftar
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>

                {{-- Hamburger (Mobile) --}}
                <button type="button"
                    @click="$store.ui.toggleMenu()"
                    class="md:hidden btn-ghost p-2.5 tap-highlight"
                    aria-label="Menu navigasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            :d="$store.ui.mobileMenuOpen
                                ? 'M6 18L18 6M6 6l12 12'
                                : 'M4 6h16M4 12h16M4 18h16'"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Mobile Search Bar ── --}}
        <div
            x-show="$store.ui.searchFocused"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 -translate-y-2"
            x-cloak
            class="md:hidden pb-3">
            <form action="{{ route('products.index') }}" method="GET">
                <div class="search-bar w-full">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="Cari produk percetakan..."
                        x-ref="mobileSearch"
                        class="flex-1 bg-transparent text-sm focus:outline-none"
                        autofocus>
                    <button type="button" @click="$store.ui.searchFocused = false"
                        class="text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Nav Kategori (Desktop) ── --}}
        <nav class="hidden md:flex items-center gap-1 h-11 overflow-x-auto scrollbar-hide border-t border-gray-100">
            <a href="{{ route('products.index') }}"
                class="nav-link text-xs {{ request()->routeIs('products.index') && !request('category') ? 'active' : '' }}">
                Semua Produk
            </a>
            @foreach(\App\Models\Category::active()->sorted()->get() as $cat)
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                    class="nav-link text-xs whitespace-nowrap {{ request('category') === $cat->slug ? 'active' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </nav>

    </div>
</header>

{{-- ── Mobile Sidebar Menu ────────────────────────────────────────────── --}}
<div
    x-show="$store.ui.mobileMenuOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    @click.self="$store.ui.closeMobileMenu()"
    class="md:hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm">

    <div
        x-show="$store.ui.mobileMenuOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="absolute left-0 top-0 bottom-0 w-72 bg-white shadow-2xl flex flex-col">

        {{-- Header Sidebar --}}
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl gradient-orange flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </div>
                <span class="font-bold text-gray-900">{{ config('printing.company.name') }}</span>
            </div>
            <button @click="$store.ui.closeMobileMenu()" class="p-2 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav Items --}}
        <nav class="flex-1 overflow-y-auto py-3">
            <div class="px-3 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-2">Kategori</p>
                <a href="{{ route('products.index') }}"
                    @click="$store.ui.closeMobileMenu()"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Semua Produk
                </a>
                @foreach(\App\Models\Category::active()->sorted()->get() as $cat)
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                        @click="$store.ui.closeMobileMenu()"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                        <span class="w-4 h-4 flex items-center justify-center text-gray-400">
                            @switch($cat->icon)
                                @case('heroicon-o-star') ⭐ @break
                                @case('heroicon-o-printer') 🖨️ @break
                                @case('heroicon-o-megaphone') 📢 @break
                                @case('heroicon-o-shopping-bag') 👕 @break
                                @case('heroicon-o-gift') 🎁 @break
                                @default ▸
                            @endswitch
                        </span>
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </nav>

        {{-- Auth Footer --}}
        <div class="p-4 border-t border-gray-100">
            @auth
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-orange-100 flex items-center justify-center">
                        <span class="text-sm font-bold text-orange-600">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full btn-ghost text-red-600 hover:bg-red-50 justify-start">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            @else
                <div class="flex gap-2">
                    <a href="{{ route('login') }}" class="flex-1 btn-secondary text-center text-sm py-2">Masuk</a>
                    <a href="{{ route('register') }}" class="flex-1 btn-primary text-center text-sm py-2">Daftar</a>
                </div>
            @endauth
        </div>
    </div>
</div>
