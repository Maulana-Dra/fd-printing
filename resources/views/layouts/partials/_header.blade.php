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
            <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center tap-highlight">
                <img src="{{ asset('assets/iconGambar.jpeg') }}" 
                     alt="{{ config('printing.company.name', 'FD Printing') }} Logo" 
                     class="h-11 w-auto object-contain">
            </a>

            {{-- Search Bar (Desktop) --}}
            <form action="{{ route('products.index') }}" method="GET"
                class="hidden md:flex flex-1 max-w-xl mx-auto">
                <div class="search-bar w-full">
                    <x-heroicon-o-magnifying-glass class="w-6 h-6 text-gray-700 flex-shrink-0" />
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
                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                </button>

                {{-- Cart Button --}}
                <button type="button"
                    @click="$store.ui.toggleCart()"
                    class="relative btn-ghost p-2.5 tap-highlight"
                    aria-label="Keranjang belanja">
                    <x-heroicon-o-shopping-cart class="w-5 h-5" />
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
                            <x-heroicon-o-user class="w-5 h-5" />
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
                                    <x-heroicon-o-home class="w-4 h-4" />
                                    Dashboard
                                </a>
                                <a href="{{ route('orders.index') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                    <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                                    Pesanan Saya
                                </a>
                                <a href="{{ route('profile.edit') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                    <x-heroicon-o-user class="w-4 h-4" />
                                    Profil Saya
                                </a>
                            </div>

                            <div class="border-t border-gray-100 py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4" />
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="py-1">
                                <a href="{{ route('login') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                    Masuk
                                </a>
                                <a href="{{ route('register') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                    <x-heroicon-o-user-plus class="w-4 h-4" />
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
                    <x-heroicon-o-x-mark x-show="$store.ui.mobileMenuOpen" class="w-5 h-5" />
                    <x-heroicon-o-bars-3 x-show="!$store.ui.mobileMenuOpen" class="w-5 h-5" />
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
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 flex-shrink-0" />
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="Cari produk percetakan..."
                        x-ref="mobileSearch"
                        class="flex-1 bg-transparent text-sm focus:outline-none"
                        autofocus>
                    <button type="button" @click="$store.ui.searchFocused = false"
                        class="text-gray-400 hover:text-gray-600 p-1">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Nav Kategori (Desktop) ── --}}
        @if (!request()->routeIs('checkout.index') && !request()->routeIs('orders.*') && !request()->routeIs('pages.*'))
        <nav class="hidden md:flex items-center gap-1 h-11 overflow-x-auto scrollbar-hide border-t border-gray-100">
            <a href="{{ route('products.index') }}"
                class="nav-link text-xs {{ request()->routeIs('products.index') && !request()->route('slug') ? 'active' : '' }}">
                Semua Produk
            </a>
            @foreach(\App\Models\Category::active()->sorted()->get() as $cat)
                <a href="{{ route('products.index', $cat->slug) }}"
                    class="nav-link text-xs whitespace-nowrap {{ request()->route('slug') === $cat->slug ? 'active' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </nav>
        @endif

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
            <div class="flex items-center">
                <img src="{{ asset('assets/iconGambar.jpeg') }}" 
                     alt="{{ config('printing.company.name') }} Logo" 
                     class="h-9 w-auto object-contain">
            </div>
            <button @click="$store.ui.closeMobileMenu()" class="p-2 rounded-lg hover:bg-gray-100">
                <x-heroicon-o-x-mark class="w-5 h-5 text-gray-500" />
            </button>
        </div>

        {{-- Nav Items --}}
        <nav class="flex-1 overflow-y-auto py-3">
            <div class="px-3 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-2">Kategori</p>
                <a href="{{ route('products.index') }}"
                    @click="$store.ui.closeMobileMenu()"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                    <x-heroicon-o-bars-4 class="w-4 h-4" />
                    Semua Produk
                </a>
                @foreach(\App\Models\Category::active()->sorted()->get() as $cat)
                    <a href="{{ route('products.index', $cat->slug) }}"
                        @click="$store.ui.closeMobileMenu()"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                        <span class="w-4 h-4 flex items-center justify-center text-gray-400">
                            <x-dynamic-component :component="$cat->icon ?? 'heroicon-o-printer'" class="w-4 h-4" />
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
                        <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4" />
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
