{{-- Cart Drawer (Slide dari kanan) --}}
<div
    x-show="$store.ui.cartDrawerOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    @click.self="$store.ui.closeCart()"
    class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm">

    <div
        x-show="$store.ui.cartDrawerOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl flex flex-col">

        {{-- Header Drawer --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h2 class="text-base font-bold text-gray-900">Keranjang</h2>
                <span class="badge-primary text-xs" x-text="`${$store.cart.count} item`"></span>
            </div>
            <button @click="$store.ui.closeCart()" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Empty State --}}
            <div
                x-show="$store.cart.count === 0"
                class="flex flex-col items-center justify-center h-full text-center p-8">
                <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-gray-900 font-semibold mb-1">Keranjang Kosong</p>
                <p class="text-gray-500 text-sm mb-6">Belum ada produk di keranjang Anda</p>
                <button @click="$store.ui.closeCart()"
                    class="btn-primary text-sm">
                    Mulai Belanja
                </button>
            </div>

            {{-- Item List --}}
            <div x-show="$store.cart.count > 0" class="p-4 space-y-3">
                <template x-for="(item, index) in $store.cart.items" :key="index">
                    <div class="flex gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        {{-- Thumbnail --}}
                        <div class="w-16 h-16 rounded-lg bg-white border border-gray-200 flex-shrink-0 overflow-hidden">
                            <img
                                :src="item.thumbnail || 'https://placehold.co/64x64/f97316/ffffff?text=P'"
                                :alt="item.name"
                                class="w-full h-full object-cover"
                            >
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 line-clamp-1" x-text="item.name"></p>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-1" x-text="item.options ? Object.values(item.options).join(' · ') : ''"></p>
                            <div class="flex items-center justify-between mt-2">
                                {{-- Qty Controls --}}
                                <div class="flex items-center gap-1.5">
                                    <button
                                        @click="$store.cart.updateQty(index, item.qty - 1)"
                                        class="w-6 h-6 rounded-md border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-gray-600 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-7 text-center text-sm font-semibold text-gray-900" x-text="item.qty"></span>
                                    <button
                                        @click="$store.cart.updateQty(index, item.qty + 1)"
                                        class="w-6 h-6 rounded-md border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-gray-600 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Price & Delete --}}
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-orange-600"
                                        x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></span>
                                    <button @click="$store.cart.remove(index)"
                                        class="p-1 rounded-md hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Footer Drawer --}}
        <div x-show="$store.cart.count > 0" class="border-t border-gray-100 p-5 space-y-3 bg-white">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Subtotal</span>
                <span class="text-base font-bold text-gray-900" x-text="$store.cart.formattedSubtotal"></span>
            </div>
            <p class="text-xs text-gray-400">Ongkos kirim dihitung saat checkout</p>
            <a href="{{ route('checkout.index') }}"
                @click="$store.ui.closeCart()"
                class="btn-primary w-full py-3 text-center">
                Lanjut ke Checkout
            </a>
            <button @click="$store.ui.closeCart()" class="btn-ghost w-full justify-center text-sm">
                Lanjut Belanja
            </button>
        </div>
    </div>
</div>
