<x-app-layout>
    <x-slot name="title">Keranjang Belanja</x-slot>
    <x-slot name="description">Periksa pesanan Anda sebelum lanjut ke checkout.</x-slot>

    <div class="container-app py-6 md:py-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium">Keranjang Belanja</span>
        </nav>

        <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-6">
            Keranjang Belanja
            @if($items->isNotEmpty())
                <span class="text-base font-semibold text-gray-400 ml-2">({{ $items->count() }} produk)</span>
            @endif
        </h1>

        {{-- ── Empty State ── --}}
        @if($items->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center max-w-sm mx-auto">
                <div class="w-24 h-24 rounded-3xl bg-orange-50 flex items-center justify-center mb-5">
                    <svg class="w-12 h-12 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Keranjang Masih Kosong</h2>
                <p class="text-gray-500 text-sm leading-relaxed mb-6">
                    Tambahkan produk percetakan yang Anda butuhkan ke keranjang terlebih dahulu.
                </p>
                <a href="{{ route('products.index') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Mulai Belanja
                </a>
            </div>

        {{-- ── Cart Content ── --}}
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8" x-data="cartPage()">

                {{-- ── Item List ── --}}
                <div class="lg:col-span-2 space-y-3">

                    {{-- Header Action --}}
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm text-gray-500">{{ $items->count() }} produk</p>
                        <form action="{{ route('cart.clear') }}" method="POST"
                            onsubmit="return confirm('Kosongkan seluruh keranjang?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">
                                Kosongkan Keranjang
                            </button>
                        </form>
                    </div>

                    @foreach($items as $itemId => $item)
                        <article
                            class="card p-4 flex gap-4"
                            x-data="cartItem({
                                id: '{{ $itemId }}',
                                unitPrice: {{ $item['unit_price'] }},
                                qty: {{ $item['quantity'] }},
                                minQty: {{ $item['min_qty'] ?? 1 }},
                            })"
                            id="cart-item-{{ $itemId }}"
                        >
                            {{-- Thumbnail --}}
                            <a href="{{ route('products.show', $item['product_slug']) }}"
                                class="flex-shrink-0 w-20 h-20 md:w-24 md:h-24 rounded-xl overflow-hidden bg-gray-100 border border-gray-200">
                                <img
                                    src="{{ $item['product_thumbnail'] }}"
                                    alt="{{ $item['product_name'] }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                    onerror="this.src='https://placehold.co/96x96/f3f4f6/9ca3af?text=P'"
                                >
                            </a>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('products.show', $item['product_slug']) }}"
                                            class="text-sm font-bold text-gray-900 hover:text-orange-600 transition-colors line-clamp-2 leading-snug block">
                                            {{ $item['product_name'] }}
                                        </a>

                                        {{-- Opsi terpilih --}}
                                        @if(! empty($item['selected_options']))
                                            <div class="flex flex-wrap gap-1 mt-1.5">
                                                @foreach($item['selected_options'] as $opt)
                                                    <span class="badge-gray text-[10px]">
                                                        {{ $opt['option_name'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Catatan --}}
                                        @if($item['design_notes'])
                                            <p class="text-xs text-gray-400 mt-1 italic line-clamp-1">
                                                Catatan: {{ $item['design_notes'] }}
                                            </p>
                                        @endif

                                        {{-- File Desain --}}
                                        @if($item['design_file_path'])
                                            <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                </svg>
                                                File desain terlampir
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Remove Button --}}
                                    <form action="{{ route('cart.remove', $itemId) }}" method="POST" class="flex-shrink-0">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all"
                                            aria-label="Hapus {{ $item['product_name'] }}"
                                            onclick="return confirm('Hapus item ini?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                {{-- Bottom: Qty + Price --}}
                                <div class="flex items-center justify-between mt-3 gap-3 flex-wrap">
                                    {{-- Qty Control --}}
                                    <form
                                        action="{{ route('cart.update', $itemId) }}"
                                        method="POST"
                                        @submit.prevent="submitUpdate($el)"
                                        class="flex items-center border border-gray-200 rounded-xl overflow-hidden bg-white">
                                        @csrf @method('PATCH')
                                        <button type="button"
                                            @click="decrement()"
                                            :disabled="qty <= minQty"
                                            class="w-9 h-9 flex items-center justify-center text-gray-500 hover:bg-gray-50 disabled:opacity-30 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                        </button>
                                        <input
                                            type="number"
                                            name="quantity"
                                            x-model.number="qty"
                                            @change="onQtyChange()"
                                            :min="minQty"
                                            class="w-14 h-9 text-center text-sm font-semibold border-x border-gray-200 focus:outline-none focus:bg-orange-50"
                                        >
                                        <button type="button"
                                            @click="increment()"
                                            class="w-9 h-9 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </form>

                                    {{-- Subtotal --}}
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400">
                                            Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                            / {{ $item['unit'] }}
                                        </p>
                                        <p class="text-base font-black text-orange-600" x-text="subtotalFormatted">
                                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    {{-- Lanjut Belanja --}}
                    <a href="{{ route('products.index') }}"
                        class="flex items-center gap-2 text-sm text-orange-600 font-medium hover:text-orange-700 transition-colors pt-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Lanjut Belanja
                    </a>
                </div>

                {{-- ── Ringkasan Pesanan ── --}}
                <aside class="lg:col-span-1">
                    <div class="card p-5 sticky top-20 space-y-4">
                        <h2 class="text-base font-bold text-gray-900">Ringkasan Pesanan</h2>

                        {{-- Item Breakdown --}}
                        <div class="space-y-2.5 text-sm">
                            @foreach($items as $item)
                                <div class="flex justify-between gap-3">
                                    <span class="text-gray-600 line-clamp-1 flex-1">
                                        {{ $item['product_name'] }}
                                        <span class="text-gray-400">×{{ $item['quantity'] }}</span>
                                    </span>
                                    <span class="font-semibold text-gray-900 flex-shrink-0">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="divider"></div>

                        {{-- Subtotal --}}
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Subtotal</span>
                            <span class="text-base font-bold text-gray-900">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- Shipping Note --}}
                        <div class="alert-info text-xs">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Ongkos kirim dihitung saat checkout berdasarkan alamat pengiriman.</span>
                        </div>

                        {{-- CTA --}}
                        @auth
                            <a href="{{ route('checkout.index') }}" class="btn-primary w-full py-3.5 text-center text-base">
                                Lanjut ke Checkout
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}?redirect={{ route('checkout.index') }}" class="btn-primary w-full py-3.5 text-center text-base">
                                Masuk untuk Checkout
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14"/>
                                </svg>
                            </a>
                            <p class="text-center text-xs text-gray-400">
                                Belum punya akun?
                                <a href="{{ route('register') }}" class="text-orange-600 font-medium hover:underline">Daftar gratis</a>
                            </p>
                        @endauth

                        {{-- WhatsApp Order --}}
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone')) }}?text={{ urlencode('Halo, saya ingin melakukan pemesanan. Total: Rp ' . number_format($total, 0, ',', '.')) }}"
                            target="_blank" rel="noopener"
                            class="flex items-center justify-center gap-2 w-full py-3 rounded-xl border-2 border-green-200 text-green-700 font-semibold text-sm hover:bg-green-50 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Order via WhatsApp
                        </a>
                    </div>
                </aside>

            </div>{{-- end grid --}}
        @endif

    </div>

    @push('scripts')
    <script>
    function cartPage() {
        return {};
    }

    function cartItem({ id, unitPrice, qty, minQty }) {
        return {
            id, unitPrice, minQty,
            qty,

            get subtotalFormatted() {
                return 'Rp ' + (this.unitPrice * this.qty).toLocaleString('id-ID');
            },

            increment() { this.qty++; this.autoSubmit(); },
            decrement() {
                if (this.qty > this.minQty) { this.qty--; this.autoSubmit(); }
            },
            onQtyChange() {
                this.qty = Math.max(parseInt(this.qty) || this.minQty, this.minQty);
                this.autoSubmit();
            },

            autoSubmit() {
                clearTimeout(this._timer);
                this._timer = setTimeout(() => {
                    fetch(`/cart/${this.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-HTTP-Method-Override': 'PATCH',
                        },
                        body: JSON.stringify({ quantity: this.qty }),
                    }).then(r => r.json()).then(data => {
                        if (data.count !== undefined) {
                            Alpine.store('cart').items = data.items || Alpine.store('cart').items;
                        }
                    }).catch(() => {});
                }, 600);
            },

            submitUpdate(form) {
                form.requestSubmit();
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
