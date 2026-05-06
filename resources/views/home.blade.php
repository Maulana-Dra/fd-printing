<x-app-layout>
    {{-- SEO Meta --}}
    <x-slot name="title">Solusi Cetak Berkualitas</x-slot>
    <x-slot name="description">{{ config('printing.company.tagline') }} — Sticker, Banner, Kaos, Merchandise, dan lebih banyak lagi.</x-slot>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 1: HERO SLIDER
    ══════════════════════════════════════════════════════════════ --}}
    <section
        class="relative overflow-hidden bg-gradient-to-br from-indigo-950 via-indigo-900 to-orange-900"
        x-data="{
            active: 0,
            total: {{ count($heroSlides) }},
            timer: null,
            start() {
                this.timer = setInterval(() => {
                    this.active = (this.active + 1) % this.total;
                }, 5500);
            },
            go(idx) {
                this.active = idx;
                clearInterval(this.timer);
                this.start();
            },
            prev() { this.go((this.active - 1 + this.total) % this.total); },
            next() { this.go((this.active + 1) % this.total); },
        }"
        x-init="start()"
        aria-label="Hero Banner"
    >
        {{-- Background dots pattern --}}
        <div class="absolute inset-0 opacity-10"
            style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 28px 28px;"></div>

        {{-- Gradient orbs --}}
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-orange-500 rounded-full opacity-20 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-indigo-500 rounded-full opacity-20 blur-3xl"></div>

        <div class="container-app relative z-10">
            <div class="py-16 md:py-24 lg:py-28">

                {{-- Slides --}}
                @foreach($heroSlides as $i => $slide)
                    <div
                        x-show="active === {{ $i }}"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0 translate-x-8"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 -translate-x-8"
                        x-cloak
                        class="max-w-3xl"
                    >
                        {{-- Badge --}}
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full
                                    bg-white/10 backdrop-blur-sm border border-white/20
                                    text-white text-sm font-medium mb-6">
                            {{ $slide['badge'] }}
                        </div>

                        {{-- Title --}}
                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white leading-tight mb-5">
                            {!! $slide['title'] !!}
                        </h1>

                        {{-- Subtitle --}}
                        <p class="text-lg text-white/75 leading-relaxed mb-8 max-w-xl">
                            {{ $slide['subtitle'] }}
                        </p>

                        {{-- CTA Buttons --}}
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ $slide['cta_url'] }}" class="btn-primary text-base px-6 py-3 shadow-lg shadow-orange-900/30">
                                {{ $slide['cta_label'] }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                            <a href="{{ $slide['cta2_url'] }}"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-base font-semibold
                                       text-white border border-white/30 hover:bg-white/10
                                       transition-all duration-200 backdrop-blur-sm">
                                {{ $slide['cta2_label'] }}
                            </a>
                        </div>
                    </div>
                @endforeach

                {{-- Slide Controls --}}
                <div class="flex items-center gap-4 mt-10">
                    {{-- Dots --}}
                    <div class="flex gap-2">
                        @foreach($heroSlides as $i => $_)
                            <button
                                @click="go({{ $i }})"
                                :class="active === {{ $i }}
                                    ? 'w-8 bg-orange-500'
                                    : 'w-2.5 bg-white/40 hover:bg-white/60'"
                                class="h-2.5 rounded-full transition-all duration-300"
                                :aria-label="'Slide {{ $i + 1 }}'">
                            </button>
                        @endforeach
                    </div>

                    {{-- Prev / Next --}}
                    <div class="flex gap-2 ml-auto">
                        <button @click="prev()"
                            class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20
                                   flex items-center justify-center text-white transition-all duration-150"
                            aria-label="Slide sebelumnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button @click="next()"
                            class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20
                                   flex items-center justify-center text-white transition-all duration-150"
                            aria-label="Slide berikutnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Bar --}}
        <div class="border-t border-white/10 bg-black/20 backdrop-blur-sm">
            <div class="container-app py-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-0 md:divide-x md:divide-white/10">
                    @foreach($stats as $stat)
                        <div class="text-center px-4 py-1">
                            <p class="text-xl md:text-2xl font-black text-white">{{ $stat['value'] }}</p>
                            <p class="text-xs text-white/60 font-medium mt-0.5">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>


    {{-- ══════════════════════════════════════════════════════════════
         SECTION 2: KATEGORI GRID
    ══════════════════════════════════════════════════════════════ --}}
    <section class="container-app mt-12 md:mt-16" aria-labelledby="section-categories">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 id="section-categories" class="section-title">Kategori Produk</h2>
                <p class="section-subtitle">Temukan produk cetak sesuai kebutuhan Anda</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn-ghost text-sm text-orange-600 hidden sm:flex">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        @php
            $categoryGradients = [
                0 => 'from-orange-500 to-red-500',
                1 => 'from-blue-500 to-indigo-600',
                2 => 'from-emerald-500 to-teal-600',
                3 => 'from-purple-500 to-violet-600',
                4 => 'from-pink-500 to-rose-600',
            ];

            $categoryIcons = [
                'heroicon-o-star'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
                'heroicon-o-printer'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>',
                'heroicon-o-megaphone'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
                'heroicon-o-shopping-bag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
                'heroicon-o-gift'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>',
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 md:gap-4">
            @foreach($categories as $i => $category)
                @php
                    $gradient = $categoryGradients[$i % 5];
                    $iconPath = $categoryIcons[$category->icon] ?? $categoryIcons['heroicon-o-printer'];
                @endphp
                <a
                    href="{{ route('products.index', ['category' => $category->slug]) }}"
                    class="card-hover group flex flex-col items-center text-center p-5 gap-3"
                    aria-label="Kategori {{ $category->name }}"
                >
                    {{-- Icon Circle --}}
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $gradient }}
                                flex items-center justify-center shadow-md
                                group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $iconPath !!}
                        </svg>
                    </div>

                    {{-- Label --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-orange-600 transition-colors line-clamp-2 leading-snug">
                            {{ $category->name }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $category->products->count() }} produk
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        <a href="{{ route('products.index') }}" class="btn-ghost text-sm text-orange-600 sm:hidden w-full justify-center mt-4">
            Lihat Semua Kategori
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </section>


    {{-- ══════════════════════════════════════════════════════════════
         SECTION 3: USP (Keunggulan)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="mt-12 md:mt-16 bg-gradient-to-r from-orange-600 to-orange-500">
        <div class="container-app py-10 md:py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 text-center text-white">
                @foreach([
                    ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Kualitas Terjamin', 'desc' => 'Bahan premium, hasil memuaskan'],
                    ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Pengerjaan Cepat', 'desc' => '1–3 hari kerja siap kirim'],
                    ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7V5z', 'title' => 'Konsultasi Gratis', 'desc' => 'Tim siap bantu via WhatsApp'],
                    ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Harga Kompetitif', 'desc' => 'Harga terbaik, tanpa biaya tersembunyi'],
                ] as $usp)
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $usp['icon'] }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm">{{ $usp['title'] }}</p>
                            <p class="text-xs text-white/80 mt-0.5">{{ $usp['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ══════════════════════════════════════════════════════════════
         SECTION 4: PRODUK PER KATEGORI
    ══════════════════════════════════════════════════════════════ --}}
    @foreach($categories as $category)
        @if($category->products->isNotEmpty())
            <section
                class="container-app mt-12 md:mt-16"
                aria-labelledby="cat-{{ $category->id }}"
                x-data
                x-intersect.once="$el.classList.add('animate-fade-in')"
                style="opacity: 0"
            >
                {{-- Section Header --}}
                <div class="flex items-end justify-between mb-5">
                    <div>
                        <h2 id="cat-{{ $category->id }}" class="section-title">
                            {{ $category->name }}
                        </h2>
                        @if($category->description)
                            <p class="section-subtitle line-clamp-1 max-w-lg">{{ $category->description }}</p>
                        @endif
                    </div>
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                        class="flex-shrink-0 btn-ghost text-sm text-orange-600">
                        Lihat Semua
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                {{-- Product Grid — desktop 4-col, tablet 3-col, mobile 2-col --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-3 md:gap-4">
                    @foreach($category->products->take(4) as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                {{-- Mobile: horizontal scroll untuk produk 5-6 --}}
                @if($category->products->count() > 4)
                    <div class="mt-3 -mx-4 px-4 md:hidden">
                        <div class="flex gap-3 overflow-x-auto scrollbar-hide pb-2">
                            @foreach($category->products->skip(4)->take(2) as $product)
                                <div class="flex-shrink-0 w-44">
                                    <x-product-card :product="$product" :compact="true" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- Desktop: tampil normal --}}
                    <div class="mt-3 hidden md:grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                        @foreach($category->products->skip(4)->take(2) as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                @endif

                {{-- CTA Kategori --}}
                <div class="mt-4 flex justify-center">
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                        class="btn-secondary text-sm">
                        Lihat Semua {{ $category->name }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </section>
        @endif
    @endforeach


    {{-- ══════════════════════════════════════════════════════════════
         SECTION 5: CTA KONSULTASI (WhatsApp)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="container-app mt-16 mb-8">
        <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-indigo-950 via-indigo-900 to-indigo-800 p-8 md:p-12">
            {{-- Background pattern --}}
            <div class="absolute inset-0 opacity-5"
                style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 24px 24px;"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-orange-500 rounded-full opacity-10 blur-3xl -translate-y-1/2 translate-x-1/2"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
                <div class="flex-1">
                    <p class="text-orange-400 text-sm font-semibold uppercase tracking-wider mb-2">💬 Butuh Bantuan?</p>
                    <h2 class="text-2xl md:text-3xl font-black text-white mb-3">
                        Konsultasikan Kebutuhan<br class="hidden md:block"> Cetak Anda
                    </h2>
                    <p class="text-white/70 text-sm leading-relaxed max-w-md mx-auto md:mx-0">
                        Tim kami siap membantu Anda memilih produk yang tepat, menghitung estimasi biaya, dan memberikan saran desain secara gratis.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 flex-shrink-0">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone')) }}?text={{ urlencode('Halo, saya ingin konsultasi kebutuhan cetak.') }}"
                        target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2.5 px-6 py-3 bg-green-500 hover:bg-green-600
                               text-white font-semibold rounded-xl transition-all duration-200
                               shadow-lg shadow-green-900/30 hover:shadow-green-900/50 hover:-translate-y-0.5">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Chat via WhatsApp
                    </a>
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-white
                               border border-white/20 hover:bg-white/10 transition-all duration-200">
                        Lihat Produk
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-app-layout>
