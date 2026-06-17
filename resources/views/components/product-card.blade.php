{{--
    Product Card Component
    Usage:
        <x-product-card :product="$product" />

    Props:
        $product  — App\Models\Product instance
        $compact  — bool, tampil versi compact (tanpa deskripsi). Default: false
--}}

@props([
    'product',
    'compact' => false,
])

<article
    class="product-card group flex flex-col"
    itemscope
    itemtype="https://schema.org/Product"
>
    {{-- Gambar --}}
    <a href="{{ route('products.show', $product->slug) }}" class="block overflow-hidden rounded-t-2xl bg-gray-100 relative">
        <img
            src="{{ $product->thumbnail_url }}"
            alt="{{ $product->name }}"
            loading="lazy"
            decoding="async"
            class="product-card-image w-full"
            width="400"
            height="400"
            itemprop="image"
            onerror="this.src='https://placehold.co/400x400/f3f4f6/9ca3af?text=Produk'"
        >

        {{-- Badge Kategori --}}
        <div class="absolute top-2.5 left-2.5">
            <span class="badge-primary text-[10px] px-2 py-0.5 backdrop-blur-sm shadow-sm">
                {{ $product->category->name ?? '' }}
            </span>
        </div>

        {{-- Overlay CTA on hover --}}
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-200 flex items-center justify-center">
            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-200
                         bg-white text-orange-600 text-xs font-semibold px-4 py-2 rounded-full shadow-lg">
                Lihat Detail
            </span>
        </div>
    </a>

    {{-- Info --}}
    <div class="p-3.5 flex flex-col flex-1">
        <a href="{{ route('products.show', $product->slug) }}" class="block" itemprop="name">
            <h3 class="text-sm font-semibold text-gray-900 line-clamp-2 leading-snug hover:text-orange-600 transition-colors">
                {{ $product->name }}
            </h3>
        </a>

        @unless($compact)
            @if($product->description)
                <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                    {{ strip_tags($product->description) }}
                </p>
            @endif
        @endunless

        {{-- Satuan & Min Order --}}
        <p class="text-[11px] text-gray-400 mt-1.5">
            Min. {{ number_format($product->min_qty) }} {{ $product->unit }}
        </p>

        {{-- Harga --}}
        <div class="mt-auto pt-3 flex items-end justify-between gap-2">
            <div itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium">Mulai dari</p>
                <p class="text-base font-bold text-orange-600 leading-tight" itemprop="price">
                    {{ $product->formatted_base_price }}
                </p>
                <meta itemprop="priceCurrency" content="IDR">
                <p class="text-[10px] text-gray-400">/ {{ $product->unit }}</p>
            </div>

            {{-- Quick Order Button --}}
            <button
                type="button"
                onclick="window.location='{{ route('products.show', $product->slug) }}'"
                class="flex-shrink-0 w-8 h-8 rounded-xl bg-orange-600 hover:bg-orange-700
                       flex items-center justify-center text-white
                       active:scale-90 transition-all duration-150 shadow-sm hover:shadow-orange-200 hover:shadow-md"
                aria-label="Pesan {{ $product->name }}"
                title="Pesan sekarang"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        </div>
    </div>
</article>
