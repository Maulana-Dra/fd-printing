<x-app-layout>
    <x-slot name="title">{{ $product->name }}</x-slot>
    <x-slot name="description">{{ Str::limit(strip_tags($product->description), 160) }}</x-slot>

    <div class="container-app py-6 md:py-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-5" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('products.index', $product->category->slug) }}" class="hover:text-orange-600 transition-colors">{{ $product->category->name }}</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium line-clamp-1">{{ $product->name }}</span>
        </nav>

        {{-- Main Product Section --}}
        <div
            class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12"
            x-data="productDetail({
                productId: {{ $product->id }},
                basePrice: {{ (float) $product->base_price }},
                minQty: {{ $product->min_qty }},
                unit: '{{ $product->unit }}',
                options: {{ $product->options->values()->toJson() }},
                csrfToken: '{{ csrf_token() }}'
            })"
            x-init="init()"
        >
            {{-- ── Kolom Kiri: Gambar ── --}}
            <div class="space-y-4">
                {{-- Gambar Utama --}}
                <div class="aspect-square rounded-2xl overflow-hidden bg-gray-100 border border-gray-200">
                    <img
                        src="{{ $product->thumbnail_url }}"
                        alt="{{ $product->name }}"
                        loading="lazy"
                        class="w-full h-full object-cover"
                        onerror="this.src='https://placehold.co/600x600/f3f4f6/9ca3af?text={{ urlencode($product->name) }}'"
                        width="600" height="600"
                        itemprop="image"
                    >
                </div>

                {{-- Info Tambahan --}}
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['label' => 'Min. Order', 'value' => number_format($product->min_qty) . ' ' . $product->unit],
                        ['label' => 'Berat/unit', 'value' => $product->weight_per_unit ? number_format($product->weight_per_unit, 2) . ' kg' : 'Hubungi kami'],
                        ['label' => 'Kategori', 'value' => $product->category->name],
                        ['label' => 'Estimasi', 'value' => config('printing.order.default_production_days') . ' hari kerja'],
                    ] as $info)
                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                            <p class="text-xs text-gray-400 mb-0.5">{{ $info['label'] }}</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $info['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Kolom Kanan: Form Konfigurasi ── --}}
            <div class="space-y-6">

                {{-- Nama & Badge --}}
                <div>
                    <span class="badge-primary text-xs mb-2 inline-block">{{ $product->category->name }}</span>
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 leading-tight">{{ $product->name }}</h1>
                    @if($product->description)
                        <div class="text-gray-500 text-sm mt-2 leading-relaxed prose prose-sm max-w-none">
                            {!! $product->description !!}
                        </div>
                    @endif
                </div>

                {{-- Harga Live --}}
                <div class="bg-gradient-to-br from-orange-50 to-orange-100/50 rounded-2xl p-5 border border-orange-200">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Estimasi Harga</p>
                    <div class="flex items-end gap-3 flex-wrap">
                        <div>
                            <p class="text-3xl font-black text-orange-600" x-text="formattedTotal" x-cloak>-</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <span x-text="formattedUnit" x-cloak></span>
                                <span class="opacity-60">/ {{ $product->unit }}</span>
                                &times; <span x-text="quantity"></span>
                            </p>
                        </div>
                        <div x-show="loading" x-cloak>
                            <div class="w-5 h-5 border-2 border-orange-400 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 line-clamp-1" x-text="optionsSummary" x-cloak></p>
                    <noscript>
                        <p class="text-2xl font-black text-orange-600">{{ $product->formatted_base_price }}</p>
                        <p class="text-xs text-gray-500">/ {{ $product->unit }}</p>
                    </noscript>
                </div>

                {{-- Opsi Produk (per grup) --}}
                @if($product->options->isNotEmpty())
                    @foreach($product->grouped_options as $groupName => $opts)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-2.5">{{ $groupName }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($opts as $opt)
                                    <label class="cursor-pointer">
                                        <input
                                            type="radio"
                                            name="opt_{{ Str::slug($groupName) }}"
                                            value="{{ $opt->id }}"
                                            class="sr-only peer"
                                            {{ $opt->is_default ? 'checked' : '' }}
                                            @change="selectOption('{{ $groupName }}', {{ $opt->id }})"
                                        >
                                        <span class="inline-flex flex-col items-center px-3.5 py-2 rounded-xl text-sm
                                                     border border-gray-200 bg-white text-gray-700
                                                     peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-700
                                                     peer-checked:font-semibold hover:border-orange-300 transition-all duration-150 cursor-pointer">
                                            {{ $opt->option_name }}
                                            @if((float) $opt->price_modifier !== 0.0)
                                                <span class="text-[10px] opacity-70 mt-0.5">{{ $opt->formatted_price_modifier }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Jumlah --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2.5">
                        Jumlah <span class="font-normal text-gray-400">(min. {{ number_format($product->min_qty) }} {{ $product->unit }})</span>
                    </h3>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden bg-white">
                            <button type="button"
                                @click="updateQty(quantity - 1)"
                                class="w-11 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors"
                                :disabled="quantity <= minQty">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                            </button>
                            <input
                                type="number"
                                x-model.number="quantity"
                                @change="updateQty($event.target.value)"
                                :min="minQty"
                                class="w-16 h-11 text-center text-sm font-semibold text-gray-900 border-x border-gray-200 focus:outline-none focus:bg-orange-50 transition-colors"
                                aria-label="Jumlah pesanan"
                            >
                            <button type="button"
                                @click="updateQty(quantity + 1)"
                                class="w-11 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">{{ $product->unit }}</span>
                    </div>
                </div>

                {{-- Upload File Desain --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">
                        Upload File Desain
                        <span class="font-normal text-gray-400">(opsional)</span>
                    </h3>
                    <p class="text-xs text-gray-400 mb-2.5">
                        Format: {{ implode(', ', config('printing.upload.allowed_extensions')) }}
                        &nbsp;·&nbsp; Maks. {{ config('printing.upload.max_upload_size_mb') }} MB per file
                    </p>

                    {{-- Drop Zone --}}
                    <div
                        class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-all duration-200 cursor-pointer"
                        :class="isDragging
                            ? 'border-orange-500 bg-orange-50'
                            : 'border-gray-300 bg-gray-50 hover:border-orange-400 hover:bg-orange-50/50'"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)"
                        @click="$refs.fileInput.click()"
                        role="button"
                        aria-label="Area upload file desain"
                    >
                        <input
                            type="file"
                            x-ref="fileInput"
                            multiple
                            accept="{{ implode(',', array_map(fn($e) => '.' . $e, config('printing.upload.allowed_extensions'))) }}"
                            class="sr-only"
                            @change="handleFileInput($event)"
                        >

                        <div x-show="uploadedFiles.length === 0">
                            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 mb-1">Drag & drop file di sini</p>
                            <p class="text-xs text-gray-400">atau klik untuk browse dari perangkat Anda</p>
                        </div>

                        {{-- File List --}}
                        <div x-show="uploadedFiles.length > 0" @click.stop class="space-y-2 text-left">
                            <template x-for="(file, i) in uploadedFiles" :key="i">
                                <div class="flex items-center gap-3 p-2.5 bg-white rounded-xl border border-gray-100 shadow-sm">
                                    {{-- Preview --}}
                                    <div class="w-10 h-10 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                        <img x-show="file.preview" :src="file.preview" class="w-full h-full object-cover" x-cloak>
                                        <svg x-show="!file.preview" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-800 truncate" x-text="file.name"></p>
                                        <p class="text-[10px] text-gray-400" x-text="file.sizeLabel"></p>
                                    </div>
                                    <button type="button" @click="removeFile(i)"
                                        class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click.stop="$refs.fileInput.click()"
                                class="w-full py-2 text-xs text-orange-600 hover:text-orange-700 font-medium rounded-xl border border-dashed border-orange-300 hover:border-orange-400 transition-colors">
                                + Tambah File
                            </button>
                        </div>

                        {{-- Error --}}
                        <p x-show="uploadError" x-text="uploadError" x-cloak
                            class="mt-2 text-xs text-red-600 font-medium"></p>
                    </div>
                </div>

                {{-- Catatan Tambahan --}}
                <div>
                    <label for="order-note" class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Catatan Pesanan <span class="font-normal text-gray-400">(opsional)</span>
                    </label>
                    <textarea
                        id="order-note"
                        x-model="note"
                        rows="2"
                        placeholder="Contoh: Tolong cetak dengan warna lebih cerah, tanpa laminasi, dll."
                        class="input resize-none text-sm"
                    ></textarea>
                </div>

                {{-- Hidden form untuk submit ke session cart (/cart/add) --}}
                <form
                    id="add-to-cart-form"
                    action="{{ route('cart.add') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="hidden"
                >
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    {{-- Diisi oleh JS sebelum submit --}}
                    <input type="hidden" name="quantity" x-ref="formQty">
                    <input type="hidden" name="notes"    x-ref="formNote">
                </form>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button
                        type="button"
                        @click="addToCart()"
                        :disabled="loading || submitting"
                        class="flex-1 btn-primary py-3.5 text-base"
                    >
                        <span x-show="!submitting">
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Tambah ke Keranjang
                        </span>
                        <span x-show="submitting" x-cloak class="flex items-center gap-2 justify-center">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            Menambahkan...
                        </span>
                    </button>
                    <a
                        :href="`https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone')) }}?text=${encodeURIComponent(waMessage())}`"
                        target="_blank" rel="noopener"
                        class="px-4 py-3.5 rounded-xl bg-green-500 hover:bg-green-600 text-white transition-colors flex items-center gap-2"
                        title="Pesan via WhatsApp"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </a>
                </div>

            </div>{{-- end kolom kanan --}}
        </div>{{-- end grid --}}


        {{-- ── Produk Serupa ── --}}
        @if($relatedProducts->isNotEmpty())
            <section class="mt-14" aria-labelledby="related-heading">
                <div class="flex items-end justify-between mb-5">
                    <div>
                        <h2 id="related-heading" class="section-title">Produk Serupa</h2>
                        <p class="section-subtitle">Dari kategori {{ $product->category->name }}</p>
                    </div>
                    <a href="{{ route('products.index', $product->category->slug) }}" class="btn-ghost text-sm text-orange-600">
                        Lihat Semua
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                    @foreach($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif

    </div>

    @push('scripts')
    <script>
    function productDetail({ productId, basePrice, minQty, unit, options, csrfToken }) {
        return {
            productId, basePrice, minQty, unit, options, csrfToken,
            quantity: minQty,
            selectedByGroup: {},
            unitPrice: basePrice,
            subtotal: basePrice * minQty,
            formattedUnit: 'Rp ' + basePrice.toLocaleString('id-ID'),
            formattedTotal: 'Rp ' + (basePrice * minQty).toLocaleString('id-ID'),
            optionsSummary: '',
            loading: false,
            submitting: false,
            uploadedFiles: [],
            isDragging: false,
            uploadError: '',
            note: '',

            init() {
                const grouped = {};
                options.forEach(opt => {
                    if (!grouped[opt.group_name]) grouped[opt.group_name] = null;
                    if (opt.is_default) grouped[opt.group_name] = opt.id;
                });
                this.selectedByGroup = grouped;
                this.calculatePrice();
            },

            get selectedOptionIds() {
                return Object.values(this.selectedByGroup).filter(v => v !== null);
            },

            selectOption(groupName, optionId) {
                this.selectedByGroup[groupName] = optionId;
                this.calculatePrice();
            },

            updateQty(val) {
                const n = parseInt(val) || this.minQty;
                this.quantity = Math.max(n, this.minQty);
                this.calculatePrice();
            },

            async calculatePrice() {
                this.loading = true;
                try {
                    const res = await fetch('/api/pricing/calculate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            product_id: this.productId,
                            quantity: this.quantity,
                            selected_options: this.selectedOptionIds,
                        }),
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();
                    this.unitPrice     = data.unit_price;
                    this.subtotal      = data.subtotal;
                    this.quantity      = data.quantity;
                    this.formattedUnit  = data.formatted_unit;
                    this.formattedTotal = data.formatted_total;
                    this.optionsSummary = data.options_summary;
                } catch (e) {
                    console.warn('Pricing calc failed:', e);
                } finally {
                    this.loading = false;
                }
            },

            // File Upload
            handleDrop(e) {
                this.isDragging = false;
                this.processFiles(e.dataTransfer.files);
            },
            handleFileInput(e) {
                this.processFiles(e.target.files);
            },
            processFiles(fileList) {
                this.uploadError = '';
                const maxMb = {{ config('printing.upload.max_upload_size_mb', 50) }};
                const allowed = {!! json_encode(config('printing.upload.allowed_extensions')) !!};

                Array.from(fileList).forEach(file => {
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!allowed.includes(ext)) {
                        this.uploadError = `Format .${ext} tidak didukung.`;
                        return;
                    }
                    if (file.size > maxMb * 1024 * 1024) {
                        this.uploadError = `File terlalu besar (maks. ${maxMb} MB).`;
                        return;
                    }
                    const entry = {
                        file, name: file.name,
                        sizeLabel: (file.size / 1024 / 1024).toFixed(2) + ' MB',
                        preview: null,
                    };
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = e => { entry.preview = e.target.result; };
                        reader.readAsDataURL(file);
                    }
                    this.uploadedFiles.push(entry);
                });
            },
            removeFile(i) { this.uploadedFiles.splice(i, 1); },

            async addToCart() {
                this.submitting = true;
                try {
                    // 1. Build FormData untuk session cart (server-side)
                    const fd = new FormData();
                    fd.append('product_id', this.productId);
                    fd.append('quantity',   this.quantity);
                    fd.append('notes',      this.note ?? '');
                    fd.append('_token',     this.csrfToken);
                    this.selectedOptionIds.forEach(id => fd.append('selected_options[]', id));
                    // Attach file desain (hanya file pertama yang dikirim)
                    if (this.uploadedFiles.length > 0) {
                        fd.append('design_file', this.uploadedFiles[0].file);
                    }

                    const res = await fetch('/cart/add', {
                        method: 'POST',
                        headers: { 
                            'Accept': 'application/json', 
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfToken 
                        },
                        body: fd,
                    });

                    const data = await res.json();

                    // 2. Update Alpine store (cart badge + drawer)
                    Alpine.store('cart').items = data.items || [];
                    Alpine.store('toast').show(data.message || 'Produk berhasil ditambahkan ke keranjang.', 'success');

                    // 3. Buka cart drawer
                    Alpine.store('ui').openCart();
                } catch (e) {
                    Alpine.store('toast').show('Gagal menambahkan ke keranjang. Coba lagi.', 'error');
                    console.error(e);
                } finally {
                    this.submitting = false;
                }
            },

            waMessage() {
                return `Halo, saya ingin memesan:\n*{{ $product->name }}*\nJumlah: ${this.quantity} {{ $product->unit }}\nTotal: ${this.formattedTotal}\nSpesifikasi: ${this.optionsSummary}\n${this.note ? 'Catatan: ' + this.note : ''}`;
            },
        };
    }
    </script>
    @endpush

</x-app-layout>
