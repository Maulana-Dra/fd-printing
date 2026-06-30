{{-- Footer --}}
<footer class="hidden md:block bg-gray-900 text-gray-300 mt-16">

    {{-- ── Kategori Footer Section ── --}}
    <div class="border-b border-gray-800/80 bg-gray-950/10">
        <div class="container-app py-5">
            <div class="flex flex-col gap-3">
                <span class="text-white font-bold text-xs uppercase tracking-wider">Kategori Produk</span>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-400">
                    @foreach(\App\Models\Category::active()->sorted()->take(6)->get() as $cat)
                        <a href="{{ route('products.index', $cat->slug) }}"
                            class="px-3.5 py-1.5 border border-transparent rounded-full hover:bg-gray-800/40 hover:border-gray-700/30 text-gray-400 hover:text-orange-400 transition-all duration-150 font-medium">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Main Footer --}}
    <div class="container-app py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- Kolom 1: Company Info --}}
            <div>
                <div class="flex items-center mb-4">
                    <img src="{{ asset('assets/iconGambar.jpeg') }}" 
                         alt="{{ config('printing.company.name') }} Logo" 
                         class="h-12 w-auto object-contain">
                </div>
                <p class="text-sm text-gray-400 leading-relaxed mb-4">
                    {{ config('printing.company.tagline') }}
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-map-pin class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5" />
                        <span class="text-gray-400">{{ config('printing.company.address') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-phone class="w-4 h-4 text-orange-500 flex-shrink-0" />
                        <a href="tel:{{ config('printing.company.phone') }}" class="text-gray-400 hover:text-orange-400 transition-colors">
                            {{ config('printing.company.phone') }}
                        </a>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-envelope class="w-4 h-4 text-orange-500 flex-shrink-0" />
                        <a href="mailto:{{ config('printing.company.email') }}" class="text-gray-400 hover:text-orange-400 transition-colors">
                            {{ config('printing.company.email') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Kolom 2: Metode Pembayaran --}}
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Metode Pembayaran</h3>
                <div class="space-y-2.5 text-sm">
                    @foreach(\App\Models\PaymentMethod::active()->sorted()->get() as $pm)
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500 flex-shrink-0"></span>
                            <span class="text-gray-400">{{ $pm->name }}</span>
                        </div>
                    @endforeach
                </div>

                <h3 class="text-white font-semibold mb-4 mt-6 text-sm uppercase tracking-wider">Pengiriman</h3>
                <div class="space-y-2.5 text-sm text-gray-400">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 flex-shrink-0"></span>
                        JNE, Sicepat, Anteraja
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 flex-shrink-0"></span>
                        Ambil di toko (Pickup)
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 flex-shrink-0"></span>
                        Same-day delivery (area tertentu)
                    </div>
                </div>
            </div>

            {{-- Kolom 3: Layanan & Info --}}
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Layanan</h3>
                <ul class="space-y-2.5 text-sm">
                    @foreach([
                        ['label' => 'Cara Pemesanan', 'route' => route('pages.how-to-order')],
                        ['label' => 'Panduan Upload Desain', 'route' => route('pages.design-guide')],
                        ['label' => 'Cek Status Pesanan', 'route' => route('orders.index')],
                        ['label' => 'Hubungi Kami', 'route' => route('pages.contact')],
                        ['label' => 'Kebijakan Privasi', 'route' => route('pages.privacy')],
                        ['label' => 'Syarat & Ketentuan', 'route' => route('pages.terms')],
                    ] as $link)
                        <li>
                            <a href="{{ $link['route'] }}" class="text-gray-400 hover:text-orange-400 transition-colors flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 flex-shrink-0"></span>
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- WhatsApp CTA --}}
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone')) }}"
                    target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 mt-6 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Chat via WhatsApp
                </a>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="border-t border-gray-800">
        <div class="container-app py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ config('printing.company.name') }}. Seluruh hak cipta dilindungi.
            </p>
            <p class="text-xs text-gray-600">
                Dibangun dengan ❤️ untuk UMKM Indonesia
            </p>
        </div>
    </div>
</footer>
