<x-app-layout>
<x-slot name="title">Checkout</x-slot>



<div class="min-h-screen bg-gray-50 py-8"
     x-data="checkoutWizard()"
     x-init="init()">

  <div class="max-w-5xl mx-auto px-4">

    {{-- ── Page Title ── --}}
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Checkout</h1>
      <p class="text-sm text-gray-500 mt-1">Selesaikan pesanan Anda dalam 3 langkah mudah</p>
    </div>

    {{-- ── Step Indicator ── --}}
    <div class="flex items-center gap-0 mb-8 bg-white rounded-2xl p-4 shadow-sm">
      <template x-for="(s, i) in steps" :key="i">
        <div class="flex items-center" :class="i < steps.length - 1 ? 'flex-1' : ''">
          <div class="flex items-center gap-2 shrink-0">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                 :class="currentStep > i+1 ? 'bg-green-500 text-white' : currentStep === i+1 ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-500'">
              <template x-if="currentStep > i+1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              </template>
              <template x-if="currentStep <= i+1">
                <span x-text="i+1"></span>
              </template>
            </div>
            <span class="text-sm font-medium hidden sm:block transition-all duration-300"
                  :class="currentStep > i+1 ? 'text-green-600 font-semibold' : currentStep === i+1 ? 'text-orange-600 font-semibold' : 'text-gray-400'"
                  x-text="s"></span>
          </div>
          <div x-show="i < steps.length - 1" class="flex-1 h-0.5 mx-3"
               :class="currentStep > i+1 ? 'bg-green-400' : 'bg-gray-200'"></div>
        </div>
      </template>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

      {{-- ── Left: Step Content ── --}}
      <div class="flex-1">

        {{-- STEP 1: Review Order ── --}}
        <div x-show="currentStep === 1" x-transition class="bg-white rounded-2xl shadow-sm p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Review Pesanan</h2>

          <div class="divide-y divide-gray-100">
            @foreach($items as $item)
            <div class="py-4 flex gap-4">
              {{-- Thumbnail --}}
              <div class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center shrink-0 overflow-hidden">
                @if($item['product_thumbnail'])
                  <img src="{{ $item['product_thumbnail'] }}" alt="{{ $item['product_name'] }}" class="w-full h-full object-cover">
                @else
                  <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                @endif
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 truncate">{{ $item['product_name'] }}</p>
                @if(!empty($item['selected_options']))
                  <p class="text-xs text-gray-500 mt-0.5">
                    {{ collect($item['selected_options'])->pluck('option_name')->filter()->implode(' · ') }}
                  </p>
                @endif
                @if($item['design_notes'])
                  <p class="text-xs text-blue-600 mt-0.5 italic">Catatan: {{ $item['design_notes'] }}</p>
                @endif
                @if($item['design_file_path'])
                  <p class="text-xs text-green-600 mt-0.5">
                    <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20"><path d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5 5 0 0112 17H8z"/></svg>
                    File desain terlampir
                  </p>
                @endif
                <div class="flex items-center justify-between mt-1">
                  <span class="text-sm text-gray-500">{{ $item['quantity'] }} {{ $item['unit'] ?? 'pcs' }}</span>
                  <span class="font-bold text-primary-700">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                </div>
              </div>
            </div>
            @endforeach
          </div>

          <div class="flex justify-end mt-6">
            <button @click="nextStep()" type="button"
                    class="btn-primary px-8 py-2.5 text-sm font-semibold rounded-xl">
              Lanjutkan ke Pengiriman
              <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        {{-- STEP 2: Shipping Info ── --}}
        <div x-show="currentStep === 2" x-transition class="bg-white rounded-2xl shadow-sm p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-5">Informasi Pengiriman</h2>

          {{-- Delivery Type Radio Cards --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <label class="border-2 rounded-xl p-4 cursor-pointer transition-all duration-200 flex items-center justify-between gap-3 w-full"
                   :class="form.delivery_type === 'pickup' ? 'border-green-600 bg-green-50/30' : 'border-gray-300 hover:border-green-400 bg-white'">
              <input type="radio" x-model="form.delivery_type" value="pickup" class="sr-only">
              <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <div class="flex-1 ml-3">
                <p class="font-semibold text-gray-900 text-sm">Ambil di Tempat</p>
                <p class="text-xs text-gray-500 mt-0.5">Gratis — Ambil langsung ke toko</p>
              </div>
              <div class="shrink-0 self-center">
                <div class="w-6 h-6 rounded-full border-2 transition-all flex items-center justify-center"
                     :class="form.delivery_type === 'pickup' ? 'border-green-500 bg-green-500' : 'border-gray-300'">
                  <svg x-show="form.delivery_type === 'pickup'" class="w-4 h-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
            </label>

            <label class="border-2 rounded-xl p-4 cursor-pointer transition-all duration-200 flex items-center justify-between gap-3 w-full"
                   :class="form.delivery_type === 'delivery' ? 'border-green-600 bg-green-50/30' : 'border-gray-300 hover:border-green-400 bg-white'">
              <input type="radio" x-model="form.delivery_type" value="delivery" class="sr-only">
              <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
              </div>
              <div class="flex-1 ml-3">
                <p class="font-semibold text-gray-900 text-sm">Dikirim (Kurir)</p>
                <p class="text-xs text-gray-500 mt-0.5">Isi alamat & pilih kurir</p>
              </div>
              <div class="shrink-0 self-center">
                <div class="w-6 h-6 rounded-full border-2 transition-all flex items-center justify-center"
                     :class="form.delivery_type === 'delivery' ? 'border-green-500 bg-green-500' : 'border-gray-300'">
                  <svg x-show="form.delivery_type === 'delivery'" class="w-4 h-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
            </label>
          </div>

          {{-- Delivery Form (conditional) --}}
          <div x-show="form.delivery_type === 'delivery'" x-transition>
            <div class="border-t border-gray-100 pt-6 mt-6 space-y-6">

              {{-- Section 1: Informasi Penerima --}}
              <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                  <span class="w-1.5 h-4 bg-orange-500 rounded-full"></span>
                  <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Informasi Penerima</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Nama Penerima <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                      </div>
                      <input type="text" x-model="form.recipient_name" 
                             class="w-full border rounded-xl pl-10 pr-4 py-2.5 text-sm bg-gray-50/50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition-all duration-200"
                             :class="errors.recipient_name ? 'border-red-300 bg-red-50/30 focus:ring-red-500/20 focus:border-red-500' : 'border-gray-200 focus:ring-orange-500/20 focus:border-orange-500'" 
                             placeholder="Nama lengkap penerima">
                    </div>
                    <p x-show="errors.recipient_name" x-text="errors.recipient_name" class="text-xs text-red-500 mt-1"></p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Nomor HP / WhatsApp <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.802-5.181-4.159-7-7.002l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                      </div>
                      <input type="tel" x-model="form.recipient_phone" 
                             class="w-full border rounded-xl pl-10 pr-4 py-2.5 text-sm bg-gray-50/50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition-all duration-200"
                             :class="errors.recipient_phone ? 'border-red-300 bg-red-50/30 focus:ring-red-500/20 focus:border-red-500' : 'border-gray-200 focus:ring-orange-500/20 focus:border-orange-500'" 
                             placeholder="08xxxxxxxxxx">
                    </div>
                    <p x-show="errors.recipient_phone" x-text="errors.recipient_phone" class="text-xs text-red-500 mt-1"></p>
                  </div>
                </div>
              </div>

              {{-- Section 2: Alamat Pengiriman --}}
              <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                  <span class="w-1.5 h-4 bg-orange-500 rounded-full"></span>
                  <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Alamat Pengiriman</h3>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Alamat Lengkap <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <div class="absolute top-3.5 left-3.5 text-gray-400">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                      </svg>
                    </div>
                    <textarea x-model="form.shipping_address" 
                              class="w-full border rounded-xl pl-10 pr-4 py-2.5 text-sm bg-gray-50/50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition-all duration-200"
                              :class="errors.shipping_address ? 'border-red-300 bg-red-50/30 focus:ring-red-500/20 focus:border-red-500' : 'border-gray-200 focus:ring-orange-500/20 focus:border-orange-500'" 
                              rows="3"
                              placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan"></textarea>
                  </div>
                  <p x-show="errors.shipping_address" x-text="errors.shipping_address" class="text-xs text-red-500 mt-1"></p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Kota / Kabupaten <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h18v18H3V3z" />
                        </svg>
                      </div>
                      <input type="text" x-model="form.shipping_city" 
                             class="w-full border rounded-xl pl-10 pr-4 py-2.5 text-sm bg-gray-50/50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition-all duration-200"
                             :class="errors.shipping_city ? 'border-red-300 bg-red-50/30 focus:ring-red-500/20 focus:border-red-500' : 'border-gray-200 focus:ring-orange-500/20 focus:border-orange-500'" 
                             placeholder="Kota / Kabupaten">
                    </div>
                    <p x-show="errors.shipping_city" x-text="errors.shipping_city" class="text-xs text-red-500 mt-1"></p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Provinsi <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                        </svg>
                      </div>
                      <input type="text" x-model="form.shipping_province" 
                             class="w-full border rounded-xl pl-10 pr-4 py-2.5 text-sm bg-gray-50/50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition-all duration-200"
                             :class="errors.shipping_province ? 'border-red-300 bg-red-50/30 focus:ring-red-500/20 focus:border-red-500' : 'border-gray-200 focus:ring-orange-500/20 focus:border-orange-500'" 
                             placeholder="Provinsi">
                    </div>
                    <p x-show="errors.shipping_province" x-text="errors.shipping_province" class="text-xs text-red-500 mt-1"></p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Kode Pos <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.008 1.24l.885 1.77a2.25 2.25 0 002.007 1.24h1.98a2.25 2.25 0 002.007-1.24l.885-1.77a2.25 2.25 0 012.007-1.24h3.86m-18 0h18a2.25 2.25 0 002.25-2.25V5.25A2.25 2.25 0 0018 3H6a2.25 2.25 0 00-2.25 2.25v6a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                      </div>
                      <input type="text" x-model="form.shipping_postal_code" 
                             class="w-full border rounded-xl pl-10 pr-4 py-2.5 text-sm bg-gray-50/50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition-all duration-200"
                             :class="errors.shipping_postal_code ? 'border-red-300 bg-red-50/30 focus:ring-red-500/20 focus:border-red-500' : 'border-gray-200 focus:ring-orange-500/20 focus:border-orange-500'" 
                             maxlength="5" placeholder="12345">
                    </div>
                    <p x-show="errors.shipping_postal_code" x-text="errors.shipping_postal_code" class="text-xs text-red-500 mt-1"></p>
                  </div>
                </div>
              </div>

              {{-- Section 3: Informasi Pengiriman & Ongkos Kirim --}}
              <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                  <span class="w-1.5 h-4 bg-amber-500 rounded-full"></span>
                  <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Metode Pengiriman & Biaya</h3>
                </div>
                
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                  <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 text-amber-600">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                      </svg>
                    </div>
                    <div class="flex-1 space-y-2">
                      <h4 class="text-sm font-bold text-amber-900">Pemberitahuan Biaya Pengiriman (Ongkir)</h4>
                      <div class="text-xs text-amber-800 leading-relaxed space-y-2">
                        <p>
                          Sistem kami <strong>tidak memungut biaya pengiriman secara online</strong> selama proses checkout ini.
                        </p>
                        <ul class="list-disc list-inside space-y-1 pl-1 font-medium text-amber-700">
                          <li>Biaya pengiriman sepenuhnya akan ditanggung oleh pemesan (penerima).</li>
                          <li>Pembayaran ongkos kirim dilakukan secara mandiri (COD / Bayar di Tempat) langsung kepada kurir saat pesanan Anda tiba.</li>
                          <li>Pilihan kurir terbaik akan ditentukan oleh tim percetakan kami demi keamanan dan efisiensi waktu pengiriman produk Anda.</li>
                        </ul>
                        <p class="text-[10px] text-amber-600 italic border-t border-amber-200/60 pt-2 mt-1">
                          *Jika ada instruksi kurir khusus, silakan tambahkan pada kolom catatan di langkah konfirmasi akhir.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <div class="flex justify-between mt-6">
            <button @click="prevStep()" type="button"
                    class="px-5 py-2.5 text-sm font-semibold text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition">
              ← Kembali
            </button>
            <button @click="validateStep2()" type="button"
                    class="btn-primary px-8 py-2.5 text-sm font-semibold rounded-xl">
              Lanjutkan ke Konfirmasi →
            </button>
          </div>
        </div>

        {{-- STEP 3: Konfirmasi ── --}}
        <div x-show="currentStep === 3" x-transition>
          <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}">
            @csrf
            <input type="hidden" name="delivery_type"         :value="form.delivery_type">
            <input type="hidden" name="recipient_name"        :value="form.recipient_name">
            <input type="hidden" name="recipient_phone"       :value="form.recipient_phone">
            <input type="hidden" name="shipping_address"      :value="form.shipping_address">
            <input type="hidden" name="shipping_city"         :value="form.shipping_city">
            <input type="hidden" name="shipping_province"     :value="form.shipping_province">
            <input type="hidden" name="shipping_postal_code"  :value="form.shipping_postal_code">
            <input type="hidden" name="courier"               :value="form.courier">
            <input type="hidden" name="courier_service"       :value="form.courier_service">
            <input type="hidden" name="shipping_cost"         :value="form.shipping_cost">
            <input type="hidden" name="notes"                 :value="form.notes">
          </form>

          <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Konfirmasi Pesanan</h2>

            {{-- Delivery Summary --}}
            <div class="rounded-xl border border-gray-200 p-4 mb-4">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Metode Pengiriman</h3>
              <template x-if="form.delivery_type === 'pickup'">
                <div class="flex items-center gap-2 text-sm text-gray-700">
                  <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                  <span class="font-medium">Ambil di Tempat</span>
                  <span class="ml-auto text-green-600 font-semibold">Gratis</span>
                </div>
              </template>
              <template x-if="form.delivery_type === 'delivery'">
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-500">Penerima</span>
                    <span class="font-medium" x-text="form.recipient_name + ' — ' + form.recipient_phone"></span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500">Alamat</span>
                    <span class="font-medium text-right max-w-xs" x-text="[form.shipping_address, form.shipping_city, form.shipping_province, form.shipping_postal_code].filter(Boolean).join(', ')"></span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-gray-500">Metode Pengiriman</span>
                    <span class="font-medium text-gray-800">Dikirim (Kurir Pilihan Percetakan)</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-gray-500">Ongkos Kirim</span>
                    <span class="font-semibold text-orange-600 bg-orange-50 border border-orange-100 rounded-lg px-2 py-0.5 text-xs">Bayar Mandiri saat Barang Tiba (COD)</span>
                  </div>
                </div>
              </template>
            </div>

            {{-- Notes --}}
            <div class="mb-4">
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Catatan untuk Percetakan (Opsional)</label>
              <textarea x-model="form.notes" 
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50/50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-200" 
                        rows="2"
                        placeholder="Contoh: hubungi saya sebelum pengiriman, warna harus sesuai pantone, dll."></textarea>
            </div>

            <div class="flex justify-between mt-4">
              <button @click="prevStep()" type="button"
                      class="px-5 py-2.5 text-sm font-semibold text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition">
                ← Kembali
              </button>
              <button @click="submitOrder()" type="button"
                      class="btn-primary px-8 py-2.5 text-sm font-semibold rounded-xl flex items-center gap-2"
                      :disabled="submitting">
                <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                <span x-text="submitting ? 'Memproses...' : '✓ Buat Pesanan'"></span>
              </button>
            </div>
          </div>
        </div>

      </div>

      {{-- ── Right: Order Summary ── --}}
      <div class="lg:w-80 shrink-0">
        <div class="bg-white rounded-2xl shadow-sm p-5 sticky top-24">
          <h3 class="font-bold text-gray-900 mb-4">Ringkasan Pesanan</h3>

          <div class="space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal ({{ count($items) }} produk)</span>
              <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-gray-600 items-center" x-show="form.delivery_type === 'delivery'">
              <span>Ongkos Kirim</span>
              <span class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-2 py-0.5">COD / Bayar di Tempat</span>
            </div>
            <div class="flex justify-between text-gray-600" x-show="form.delivery_type === 'pickup'">
              <span>Ongkos Kirim</span>
              <span class="text-green-600 font-medium">Gratis</span>
            </div>
            <div class="border-t border-gray-200 pt-2 mt-2 flex justify-between font-bold text-gray-900 text-base">
              <span>Total</span>
              <span x-text="'Rp ' + totalAmount.toLocaleString('id-ID')" class="text-primary-700"></span>
            </div>
          </div>

          <p class="text-xs text-gray-400 mt-4 text-center">
            Pembayaran dilakukan setelah order dibuat.<br>
            Upload bukti transfer / scan QRIS.
          </p>
        </div>
      </div>

    </div>
  </div>

@push('scripts')
<script>
function checkoutWizard() {
    return {
        currentStep: 1,
        submitting: false,
        steps: ['Review Pesanan', 'Info Pengiriman', 'Konfirmasi'],
        form: {
            delivery_type:        'pickup',
            recipient_name:       '{{ old('recipient_name', $user?->name ?? '') }}',
            recipient_phone:      '{{ old('recipient_phone', $user?->phone ?? '') }}',
            shipping_address:     '{{ old('shipping_address', $user?->address ?? '') }}',
            shipping_city:        '{{ old('shipping_city', '') }}',
            shipping_province:    '{{ old('shipping_province', '') }}',
            shipping_postal_code: '{{ old('shipping_postal_code', '') }}',
            courier:              '{{ old('courier', '') }}',
            courier_service:      '{{ old('courier_service', '') }}',
            shipping_cost:        {{ old('shipping_cost', 0) }},
            notes:                '{{ old('notes', '') }}',
        },
        errors: {},
        subtotal: {{ $subtotal }},
        get totalAmount() {
            const shipping = this.form.delivery_type === 'delivery' ? (parseFloat(this.form.shipping_cost) || 0) : 0;
            return this.subtotal + shipping;
        },
        init() {
            @if($errors->any())
            this.currentStep = {{ old('delivery_type') ? 2 : 1 }};
            @endif
        },
        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        updateTotal() {},
        validateStep2() {
            this.errors = {};
            const f = this.form;
            if (!f.delivery_type) {
                this.errors.delivery_type = 'Pilih metode pengiriman.'; return;
            }
            if (f.delivery_type === 'delivery') {
                if (!f.recipient_name)    this.errors.recipient_name    = 'Nama penerima wajib diisi.';
                if (!f.recipient_phone)   this.errors.recipient_phone   = 'Nomor HP wajib diisi.';
                if (!f.shipping_address)  this.errors.shipping_address  = 'Alamat wajib diisi.';
                if (!f.shipping_city)     this.errors.shipping_city     = 'Kota wajib diisi.';
                if (!f.shipping_province) this.errors.shipping_province = 'Provinsi wajib diisi.';
                if (!f.shipping_postal_code) this.errors.shipping_postal_code = 'Kode pos wajib diisi.';
            }
            if (Object.keys(this.errors).length === 0) this.nextStep();
        },
        submitOrder() {
            this.submitting = true;
            document.getElementById('checkout-form').submit();
        },
    };
}
</script>
@endpush
</x-app-layout>
