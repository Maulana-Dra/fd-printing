<x-app-layout>
<x-slot name="title">Checkout</x-slot>

@push('head')
<style>
.step-indicator { @apply flex items-center gap-2 text-sm font-medium; }
.step-circle { @apply w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300; }
.step-circle.active { @apply bg-primary-600 text-white; }
.step-circle.done { @apply bg-green-500 text-white; }
.step-circle.inactive { @apply bg-gray-200 text-gray-500; }
.step-label.active { @apply text-primary-600 font-semibold; }
.step-label.done { @apply text-green-600; }
.step-label.inactive { @apply text-gray-400; }
.form-label { @apply block text-sm font-medium text-gray-700 mb-1; }
.form-input { @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition; }
.form-input.error { @apply border-red-400 focus:ring-red-400; }
.radio-card { @apply border-2 rounded-xl p-4 cursor-pointer transition-all duration-200 flex items-start gap-3; }
.radio-card.selected { @apply border-primary-500 bg-primary-50; }
.radio-card.unselected { @apply border-gray-200 hover:border-primary-300 bg-white; }
</style>
@endpush

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
            <div class="step-circle"
                 :class="currentStep > i+1 ? 'done' : currentStep === i+1 ? 'active' : 'inactive'">
              <template x-if="currentStep > i+1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              </template>
              <template x-if="currentStep <= i+1">
                <span x-text="i+1"></span>
              </template>
            </div>
            <span class="text-sm font-medium hidden sm:block step-label"
                  :class="currentStep > i+1 ? 'done' : currentStep === i+1 ? 'active' : 'inactive'"
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
            <label class="radio-card cursor-pointer"
                   :class="form.delivery_type === 'pickup' ? 'selected' : 'unselected'">
              <input type="radio" x-model="form.delivery_type" value="pickup" class="sr-only">
              <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <div>
                <p class="font-semibold text-gray-900 text-sm">Ambil di Tempat</p>
                <p class="text-xs text-gray-500 mt-0.5">Gratis — Ambil langsung ke toko</p>
              </div>
            </label>

            <label class="radio-card cursor-pointer"
                   :class="form.delivery_type === 'delivery' ? 'selected' : 'unselected'">
              <input type="radio" x-model="form.delivery_type" value="delivery" class="sr-only">
              <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
              </div>
              <div>
                <p class="font-semibold text-gray-900 text-sm">Dikirim (Kurir)</p>
                <p class="text-xs text-gray-500 mt-0.5">Isi alamat & pilih kurir</p>
              </div>
            </label>
          </div>

          {{-- Delivery Form (conditional) --}}
          <div x-show="form.delivery_type === 'delivery'" x-transition>
            <div class="border-t border-gray-100 pt-5 space-y-4">

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Nama Penerima <span class="text-red-500">*</span></label>
                  <input type="text" x-model="form.recipient_name" class="form-input"
                         :class="errors.recipient_name ? 'error' : ''" placeholder="Nama lengkap penerima">
                  <p x-show="errors.recipient_name" x-text="errors.recipient_name" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                  <label class="form-label">Nomor HP <span class="text-red-500">*</span></label>
                  <input type="tel" x-model="form.recipient_phone" class="form-input"
                         :class="errors.recipient_phone ? 'error' : ''" placeholder="08xxxxxxxxxx">
                  <p x-show="errors.recipient_phone" x-text="errors.recipient_phone" class="text-xs text-red-500 mt-1"></p>
                </div>
              </div>

              <div>
                <label class="form-label">Alamat Lengkap <span class="text-red-500">*</span></label>
                <textarea x-model="form.shipping_address" class="form-input" rows="2"
                          :class="errors.shipping_address ? 'error' : ''"
                          placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan"></textarea>
                <p x-show="errors.shipping_address" x-text="errors.shipping_address" class="text-xs text-red-500 mt-1"></p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="form-label">Kota <span class="text-red-500">*</span></label>
                  <input type="text" x-model="form.shipping_city" class="form-input"
                         :class="errors.shipping_city ? 'error' : ''" placeholder="Kota / Kabupaten">
                  <p x-show="errors.shipping_city" x-text="errors.shipping_city" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                  <label class="form-label">Provinsi <span class="text-red-500">*</span></label>
                  <input type="text" x-model="form.shipping_province" class="form-input"
                         :class="errors.shipping_province ? 'error' : ''" placeholder="Provinsi">
                  <p x-show="errors.shipping_province" x-text="errors.shipping_province" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                  <label class="form-label">Kode Pos <span class="text-red-500">*</span></label>
                  <input type="text" x-model="form.shipping_postal_code" class="form-input"
                         :class="errors.shipping_postal_code ? 'error' : ''" maxlength="5" placeholder="12345">
                  <p x-show="errors.shipping_postal_code" x-text="errors.shipping_postal_code" class="text-xs text-red-500 mt-1"></p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Jasa Kurir <span class="text-red-500">*</span></label>
                  <select x-model="form.courier" class="form-input" :class="errors.courier ? 'error' : ''">
                    <option value="">-- Pilih Kurir --</option>
                    @foreach($couriers as $val => $label)
                      <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <p x-show="errors.courier" x-text="errors.courier" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                  <label class="form-label">Layanan (Opsional)</label>
                  <input type="text" x-model="form.courier_service" class="form-input"
                         placeholder="Contoh: REG, YES, OKE">
                </div>
              </div>

              <div>
                <label class="form-label">Estimasi Ongkos Kirim <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-medium">Rp</span>
                  <input type="number" x-model="form.shipping_cost" class="form-input pl-10"
                         :class="errors.shipping_cost ? 'error' : ''"
                         min="0" step="1000" placeholder="0"
                         @input="updateTotal()">
                </div>
                <p class="text-xs text-gray-500 mt-1">Cek ongkir di website kurir & masukkan secara manual</p>
                <p x-show="errors.shipping_cost" x-text="errors.shipping_cost" class="text-xs text-red-500 mt-1"></p>
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
                  <div class="flex justify-between">
                    <span class="text-gray-500">Kurir</span>
                    <span class="font-medium" x-text="form.courier.toUpperCase() + (form.courier_service ? ' — ' + form.courier_service.toUpperCase() : '')"></span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500">Ongkos Kirim</span>
                    <span class="font-semibold text-blue-700" x-text="'Rp ' + Number(form.shipping_cost || 0).toLocaleString('id-ID')"></span>
                  </div>
                </div>
              </template>
            </div>

            {{-- Notes --}}
            <div class="mb-4">
              <label class="form-label">Catatan untuk Percetakan (Opsional)</label>
              <textarea x-model="form.notes" class="form-input" rows="2"
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
              <span>Subtotal ({{ $items->count() }} produk)</span>
              <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-gray-600" x-show="form.delivery_type === 'delivery'">
              <span>Ongkos Kirim</span>
              <span x-text="'Rp ' + Number(form.shipping_cost || 0).toLocaleString('id-ID')"></span>
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
        nextStep() { if (this.currentStep < 3) this.currentStep++; },
        prevStep() { if (this.currentStep > 1) this.currentStep--; },
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
                if (!f.courier)           this.errors.courier           = 'Pilih jasa kurir.';
                if (!f.shipping_cost && f.shipping_cost !== 0) this.errors.shipping_cost = 'Ongkir wajib diisi.';
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
