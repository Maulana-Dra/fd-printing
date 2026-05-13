<x-app-layout>
<x-slot name="title">Pembayaran Order #{{ $order->order_number }}</x-slot>

<div class="min-h-screen bg-gray-50 py-8" x-data="paymentPage()">
  <div class="max-w-4xl mx-auto px-4">

    {{-- ── Header ── --}}
    <div class="mb-6 flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <h1 class="text-xl font-bold text-gray-900">Selesaikan Pembayaran</h1>
        <p class="text-sm text-gray-500">Order <span class="font-semibold text-primary-700">{{ $order->order_number }}</span></p>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

      {{-- ── Left: Payment Methods + Confirmation Form ── --}}
      <div class="flex-1 space-y-5">

        {{-- Order Summary Card --}}
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
          <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Ringkasan Order</h2>
          <div class="space-y-2">
            @foreach($order->items as $item)
            <div class="flex justify-between text-sm">
              <span class="text-gray-700">{{ $item->product_name }} <span class="text-gray-400">×{{ $item->quantity }}</span></span>
              <span class="font-medium">{{ $item->formatted_subtotal }}</span>
            </div>
            @endforeach
            @if($order->shipping_cost > 0)
            <div class="flex justify-between text-sm text-gray-600 border-t border-dashed border-gray-200 pt-2 mt-2">
              <span>Ongkos Kirim ({{ strtoupper($order->courier ?? '-') }})</span>
              <span>{{ $order->formatted_shipping_cost }}</span>
            </div>
            @endif
            <div class="flex justify-between font-bold text-base border-t border-gray-200 pt-2 mt-2">
              <span>Total Pembayaran</span>
              <span class="text-primary-700">{{ $order->formatted_total_amount }}</span>
            </div>
          </div>
        </div>

        {{-- Payment Method Tabs --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          {{-- Tab Headers --}}
          <div class="flex border-b border-gray-200">
            @php $tabTypes = ['qris' => 'QRIS', 'bank_transfer' => 'Transfer Bank', 'ewallet' => 'Dompet Digital']; @endphp
            @foreach($tabTypes as $typeKey => $typeLabel)
              @if(isset($paymentMethods[$typeKey]) && $paymentMethods[$typeKey]->count() > 0)
              <button @click="activeTab = '{{ $typeKey }}'"
                      :class="activeTab === '{{ $typeKey }}'
                              ? 'border-b-2 border-primary-600 text-primary-700 bg-primary-50'
                              : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                      class="flex-1 py-3 px-2 text-sm font-medium transition-all duration-200 focus:outline-none">
                {{ $typeLabel }}
              </button>
              @endif
            @endforeach
          </div>

          {{-- Tab: QRIS --}}
          @if(isset($paymentMethods['qris']))
          <div x-show="activeTab === 'qris'" class="p-5">
            @foreach($paymentMethods['qris'] as $pm)
            <div class="text-center">
              <p class="font-semibold text-gray-800 mb-1">{{ $pm->name }}</p>
              @if($pm->description)
                <p class="text-sm text-gray-500 mb-3">{{ $pm->description }}</p>
              @endif
              @if($pm->qr_image_url)
                <div class="inline-block p-3 border-2 border-gray-200 rounded-2xl bg-white">
                  <img src="{{ $pm->qr_image_url }}" alt="QR Code {{ $pm->name }}"
                       class="w-52 h-52 object-contain mx-auto">
                </div>
              @else
                <div class="w-52 h-52 mx-auto bg-gray-100 rounded-2xl flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-300">
                  <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                  <p class="text-xs text-gray-400">QR belum tersedia</p>
                </div>
              @endif
              <p class="text-xs text-gray-500 mt-3">Scan QR Code di atas menggunakan aplikasi mobile banking atau dompet digital apapun</p>
              <button @click="selectMethod({{ $pm->id }}, 'qris', '{{ $pm->name }}')"
                      :class="selectedMethodId === {{ $pm->id }} ? 'bg-primary-600 text-white' : 'border border-primary-600 text-primary-700 hover:bg-primary-50'"
                      class="mt-4 px-6 py-2 rounded-xl text-sm font-semibold transition">
                Saya Sudah Bayar via QRIS
              </button>
            </div>
            @endforeach
          </div>
          @endif

          {{-- Tab: Transfer Bank --}}
          @if(isset($paymentMethods['bank_transfer']))
          <div x-show="activeTab === 'bank_transfer'" class="p-5 space-y-3">
            @foreach($paymentMethods['bank_transfer'] as $pm)
            <div class="border rounded-xl p-4 cursor-pointer transition-all duration-200"
                 :class="selectedMethodId === {{ $pm->id }} ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'"
                 @click="selectMethod({{ $pm->id }}, 'bank_transfer', '{{ $pm->name }}')">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-bold text-gray-900 text-sm">{{ $pm->bank_name ?? $pm->name }}</p>
                  <p class="text-lg font-mono font-bold text-primary-700 mt-1 tracking-wider">{{ $pm->account_number }}</p>
                  <p class="text-sm text-gray-500">a.n. {{ $pm->account_name }}</p>
                </div>
                <div class="shrink-0">
                  <div class="w-6 h-6 rounded-full border-2 transition-all"
                       :class="selectedMethodId === {{ $pm->id }} ? 'border-primary-500 bg-primary-500' : 'border-gray-300'">
                    <svg x-show="selectedMethodId === {{ $pm->id }}" class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  </div>
                </div>
              </div>
              @if($pm->description)
              <p class="text-xs text-gray-500 mt-2 border-t border-gray-100 pt-2">{{ $pm->description }}</p>
              @endif
            </div>
            @endforeach
            <p class="text-xs text-amber-700 bg-amber-50 rounded-xl p-3 flex gap-2">
              <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
              Pastikan transfer tepat sejumlah <strong>{{ $order->formatted_total_amount }}</strong> agar pembayaran lebih cepat diverifikasi.
            </p>
          </div>
          @endif

          {{-- Tab: e-Wallet --}}
          @if(isset($paymentMethods['ewallet']))
          <div x-show="activeTab === 'ewallet'" class="p-5 space-y-3">
            @foreach($paymentMethods['ewallet'] as $pm)
            <div class="border rounded-xl p-4 cursor-pointer transition-all duration-200"
                 :class="selectedMethodId === {{ $pm->id }} ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'"
                 @click="selectMethod({{ $pm->id }}, 'ewallet', '{{ $pm->name }}')">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-bold text-gray-900 text-sm">{{ $pm->name }}</p>
                  <p class="text-base font-mono font-semibold text-primary-700 mt-0.5">{{ $pm->account_number }}</p>
                  <p class="text-sm text-gray-500">a.n. {{ $pm->account_name }}</p>
                </div>
                <div class="w-6 h-6 rounded-full border-2 transition-all"
                     :class="selectedMethodId === {{ $pm->id }} ? 'border-primary-500 bg-primary-500' : 'border-gray-300'">
                  <svg x-show="selectedMethodId === {{ $pm->id }}" class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @endif
        </div>

        {{-- Confirmation Form --}}
        <div x-show="selectedMethodId !== null" x-transition
             class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-base font-bold text-gray-900 mb-5">
            Upload Bukti Pembayaran
            <span class="text-sm font-normal text-gray-500 ml-1" x-text="'via ' + selectedMethodName"></span>
          </h2>

          @if($errors->any())
          <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
            <ul class="text-sm text-red-700 space-y-1">
              @foreach($errors->all() as $error)
                <li class="flex items-start gap-2">
                  <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                  {{ $error }}
                </li>
              @endforeach
            </ul>
          </div>
          @endif

          <form method="POST" action="{{ route('orders.payment.confirm', $order) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="payment_method_id" :value="selectedMethodId">

            <div class="space-y-4">
              {{-- Nominal --}}
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Jumlah Transfer <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-medium">Rp</span>
                  <input type="number" name="amount_paid" required
                         value="{{ old('amount_paid', (int) $order->total_amount) }}"
                         min="1" step="100"
                         class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('amount_paid') border-red-400 @enderror">
                </div>
              </div>

              {{-- Tanggal Transfer --}}
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Tanggal Transfer <span class="text-red-500">*</span>
                </label>
                <input type="date" name="transfer_date" required
                       value="{{ old('transfer_date', date('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('transfer_date') border-red-400 @enderror">
              </div>

              {{-- Upload Bukti --}}
              <div x-data="fileUploadZone()">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Foto Bukti Pembayaran <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed rounded-xl p-5 text-center transition-all duration-200 cursor-pointer"
                     :class="isDragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-400 bg-gray-50'"
                     @dragover.prevent="isDragging = true"
                     @dragleave="isDragging = false"
                     @drop.prevent="handleDrop($event)"
                     @click="$refs.proofInput.click()">
                  <input type="file" name="proof_image" x-ref="proofInput"
                         accept=".jpg,.jpeg,.png,.webp,.pdf" required class="sr-only"
                         @change="handleFile($event.target.files[0])">
                  <template x-if="!preview">
                    <div>
                      <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                      <p class="text-sm text-gray-600 font-medium">Klik atau seret file ke sini</p>
                      <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP, PDF — maks. {{ config('printing.upload.max_proof_size_mb', 5) }} MB</p>
                    </div>
                  </template>
                  <template x-if="preview">
                    <div>
                      <img :src="preview" alt="Preview" class="max-h-40 mx-auto rounded-lg object-contain mb-2">
                      <p class="text-xs text-gray-500" x-text="fileName"></p>
                      <button type="button" @click.stop="clearFile()"
                              class="text-xs text-red-500 hover:text-red-700 mt-1">Ganti file</button>
                    </div>
                  </template>
                </div>
                <p x-show="uploadError" x-text="uploadError" class="text-xs text-red-500 mt-1"></p>
              </div>

              {{-- Catatan --}}
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                <textarea name="notes" rows="2" maxlength="500"
                          placeholder="Contoh: Transfer dari BCA ke BCA, nama pengirim: Andi"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('notes') }}</textarea>
              </div>

              {{-- Submit --}}
              <button type="submit"
                      class="w-full bg-primary-600 hover:bg-primary-700 text-gray-700 border border-gray-300 font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Kirim Konfirmasi Pembayaran
              </button>
            </div>
          </form>
        </div>

        {{-- Prompt to select method --}}
        <div x-show="selectedMethodId === null"
             class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-center text-amber-700 text-sm">
          <svg class="w-8 h-8 mx-auto mb-2 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>
          Pilih metode pembayaran di atas, lalu scroll ke bawah untuk mengisi bukti pembayaran.
        </div>

      </div>

      {{-- ── Right: Info Box ── --}}
      <div class="lg:w-72 shrink-0">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sticky top-24 space-y-4">
          <h3 class="font-bold text-gray-900 text-sm">Informasi Order</h3>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Nomor Order</span>
              <span class="font-mono font-semibold text-gray-800">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Status</span>
              <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                {{ $order->status->label() }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Pengiriman</span>
              <span class="font-medium">{{ $order->delivery_type->label() }}</span>
            </div>
            @if($order->courier)
            <div class="flex justify-between">
              <span class="text-gray-500">Kurir</span>
              <span class="font-medium">{{ strtoupper($order->courier) }}</span>
            </div>
            @endif
            <div class="border-t border-gray-100 pt-2 flex justify-between font-bold">
              <span>Total</span>
              <span class="text-primary-700">{{ $order->formatted_total_amount }}</span>
            </div>
          </div>

          <div class="bg-blue-50 rounded-xl p-3 text-xs text-blue-700 space-y-1">
            <p class="font-semibold">Cara Konfirmasi:</p>
            <ol class="list-decimal list-inside space-y-0.5 text-blue-600">
              <li>Lakukan pembayaran sesuai total</li>
              <li>Screenshot/foto bukti transfer</li>
              <li>Isi form konfirmasi di kiri</li>
              <li>Tim kami verifikasi dalam 1×24 jam</li>
            </ol>
          </div>

          <a href="{{ route('orders.index') }}"
             class="block text-center text-sm text-gray-500 hover:text-gray-700 underline underline-offset-2">
            Lihat semua pesanan
          </a>
        </div>
      </div>

    </div>
  </div>

@push('scripts')
<script>
function paymentPage() {
    return {
        activeTab: '{{ isset($paymentMethods['qris']) && $paymentMethods['qris']->count() ? 'qris' : (isset($paymentMethods['bank_transfer']) ? 'bank_transfer' : 'ewallet') }}',
        selectedMethodId: null,
        selectedMethodName: '',
        selectMethod(id, type, name) {
            this.selectedMethodId = id;
            this.selectedMethodName = name;
            this.activeTab = type;
            this.$nextTick(() => {
                document.querySelector('[x-show="selectedMethodId !== null"]')
                    ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },
    };
}

function fileUploadZone() {
    return {
        isDragging: false,
        preview: null,
        fileName: '',
        uploadError: '',
        maxMb: {{ config('printing.upload.max_proof_size_mb', 5) }},
        handleFile(file) {
            if (!file) return;
            this.uploadError = '';
            if (file.size > this.maxMb * 1024 * 1024) {
                this.uploadError = `File terlalu besar (maks. ${this.maxMb} MB).`;
                return;
            }
            this.fileName = file.name;
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => { this.preview = e.target.result; };
                reader.readAsDataURL(file);
            } else {
                this.preview = null; // PDF — no preview
                this.fileName = `📄 ${file.name}`;
            }
        },
        handleDrop(e) {
            this.isDragging = false;
            const file = e.dataTransfer.files[0];
            if (file) {
                this.$refs.proofInput.files = e.dataTransfer.files;
                this.handleFile(file);
            }
        },
        clearFile() {
            this.preview = null;
            this.fileName = '';
            this.$refs.proofInput.value = '';
        },
    };
}
</script>
@endpush
</x-app-layout>
