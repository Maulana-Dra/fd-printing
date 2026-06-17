<x-app-layout>
    <x-slot name="title">{{ $category ? $category->name : 'Semua Produk' }}</x-slot>
    <x-slot name="description">{{ $category?->description ?? 'Temukan produk percetakan berkualitas untuk kebutuhan bisnis Anda.' }}</x-slot>

    <div class="container-app py-6 md:py-8">

        {{-- ── Breadcrumb ── --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-5" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            @if($category)
                <a href="{{ route('products.index') }}" class="hover:text-orange-600 transition-colors">Produk</a>
                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium">{{ $category->name }}</span>
            @else
                <span class="text-gray-900 font-medium">Semua Produk</span>
            @endif
        </nav>

        {{-- ── Category Chips ── --}}
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide pb-3 mb-5 -mx-4 px-4 md:mx-0 md:px-0">
            <a href="{{ route('products.index') }}"
                class="category-chip flex-shrink-0 {{ ! $category ? 'active' : '' }}">
                Semua
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('products.index', $cat->slug) }}"
                    class="category-chip flex-shrink-0 {{ $category?->id === $cat->id ? 'active' : '' }}">
                    {{ $cat->name }}
                    <span class="opacity-60 text-[10px]">({{ $cat->active_products_count }})</span>
                </a>
            @endforeach
        </div>

        {{-- ── Filter Bar ── --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-6" x-data>
            {{-- Search --}}
            <form method="GET" action="{{ route('products.index', $category?->slug) }}"
                class="flex-1 flex">
                <div class="search-bar w-full">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari produk{{ $category ? ' di ' . $category->name : '' }}..."
                        class="flex-1 bg-transparent text-sm focus:outline-none text-gray-900 placeholder-gray-400"
                    >
                    @if(request('q'))
                        <a href="{{ route('products.index', $category?->slug) }}"
                            class="text-gray-400 hover:text-gray-600 p-1 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                </div>
            </form>

            {{-- Sort --}}
            <div class="flex-shrink-0">
                <form method="GET" action="{{ route('products.index', $category?->slug) }}" id="sort-form">
                    @if(request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <select
                        name="sort"
                        onchange="document.getElementById('sort-form').submit()"
                        class="input text-xs sm:text-sm w-full sm:w-48 cursor-pointer px-2 sm:px-4"
                        aria-label="Urutkan produk">
                        <option value="default"     {{ request('sort', 'default') === 'default'    ? 'selected' : '' }}>Relevansi</option>
                        <option value="price_asc"   {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>Termurah</option>
                        <option value="price_desc"  {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Termahal</option>
                        <option value="newest"      {{ request('sort') === 'newest'     ? 'selected' : '' }}>Terbaru</option>
                    </select>
                </form>
            </div>
        </div>

        {{-- ── Results Info ── --}}
        @if(request('q') || $category)
            <p class="text-sm text-gray-500 mb-4">
                Menampilkan
                <span class="font-semibold text-gray-900">{{ $products->total() }}</span>
                produk
                @if(request('q'))
                    untuk "<span class="font-semibold text-orange-600">{{ request('q') }}</span>"
                @endif
                @if($category)
                    dalam <span class="font-semibold text-gray-900">{{ $category->name }}</span>
                @endif
            </p>
        @endif

        {{-- ── Empty State ── --}}
        @if($products->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h2 class="text-gray-900 font-bold text-lg mb-1">Produk Tidak Ditemukan</h2>
                <p class="text-gray-500 text-sm mb-6 max-w-sm">
                    @if(request('q'))
                        Tidak ada produk yang cocok dengan "<strong>{{ request('q') }}</strong>".
                        Coba kata kunci lain atau lihat semua produk.
                    @else
                        Belum ada produk aktif di kategori ini.
                    @endif
                </p>
                <a href="{{ route('products.index') }}" class="btn-primary">Lihat Semua Produk</a>
            </div>

        {{-- ── Product Grid ── --}}
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            {{-- ── Pagination ── --}}
            @if($products->hasPages())
                <div class="mt-8 flex justify-center">
                    <nav class="flex items-center gap-1" aria-label="Navigasi halaman">
                        {{-- Prev --}}
                        @if($products->onFirstPage())
                            <span class="px-3 py-2 rounded-xl text-sm text-gray-300 cursor-not-allowed select-none">
                                ‹ Sebelumnya
                            </span>
                        @else
                            <a href="{{ $products->previousPageUrl() }}"
                                class="px-3 py-2 rounded-xl text-sm text-gray-600 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                ‹ Sebelumnya
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach($products->getUrlRange(
                            max(1, $products->currentPage() - 2),
                            min($products->lastPage(), $products->currentPage() + 2)
                        ) as $page => $url)
                            @if($page === $products->currentPage())
                                <span class="w-9 h-9 rounded-xl text-sm font-bold bg-orange-600 text-white
                                             flex items-center justify-center">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                    class="w-9 h-9 rounded-xl text-sm text-gray-600 hover:bg-orange-50 hover:text-orange-600
                                           flex items-center justify-center transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}"
                                class="px-3 py-2 rounded-xl text-sm text-gray-600 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                Selanjutnya ›
                            </a>
                        @else
                            <span class="px-3 py-2 rounded-xl text-sm text-gray-300 cursor-not-allowed select-none">
                                Selanjutnya ›
                            </span>
                        @endif
                    </nav>
                </div>
                <p class="text-center text-xs text-gray-400 mt-3">
                    Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}
                    · {{ $products->total() }} produk
                </p>
            @endif
        @endif

    </div>
</x-app-layout>
