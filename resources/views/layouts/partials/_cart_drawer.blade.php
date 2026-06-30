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
                <x-heroicon-o-shopping-cart class="w-5 h-5 text-orange-600" />
                <h2 class="text-base font-bold text-gray-900">Keranjang</h2>
                <span class="badge-primary text-xs" x-text="`${$store.cart.count} produk`"></span>
            </div>
            <button @click="$store.ui.closeCart()" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <x-heroicon-o-x-mark class="w-5 h-5 text-gray-500" />
            </button>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Empty State --}}
            <div
                x-show="$store.cart.count === 0"
                class="flex flex-col items-center justify-center h-full text-center p-8">
                <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                    <x-heroicon-o-shopping-cart class="w-10 h-10 text-gray-300" />
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
                                :src="item.product_thumbnail || 'https://placehold.co/64x64/f97316/ffffff?text=P'"
                                :alt="item.product_name"
                                class="w-full h-full object-cover"
                            >
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 line-clamp-1" x-text="item.product_name"></p>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-1" x-text="item.selected_options ? item.selected_options.map(o => o.option_name).filter(Boolean).join(' · ') : ''"></p>
                            <div class="flex items-center justify-between mt-2">
                                {{-- Qty Controls --}}
                                <div class="flex items-center gap-1.5">
                                    <button
                                        @click="$store.cart.updateQty(item.id, item.quantity - 1)"
                                        class="w-6 h-6 rounded-md border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-gray-600 transition-colors">
                                        <x-heroicon-o-minus class="w-3 h-3" />
                                    </button>
                                    <span class="w-7 text-center text-sm font-semibold text-gray-900" x-text="item.quantity"></span>
                                    <button
                                        @click="$store.cart.updateQty(item.id, item.quantity + 1)"
                                        class="w-6 h-6 rounded-md border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-gray-600 transition-colors">
                                        <x-heroicon-o-plus class="w-3 h-3" />
                                    </button>
                                </div>

                                {{-- Price & Delete --}}
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-orange-600"
                                        x-text="'Rp ' + (item.unit_price * item.quantity).toLocaleString('id-ID')"></span>
                                    <button @click="$store.cart.remove(item.id)"
                                        class="p-1 rounded-md hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
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
