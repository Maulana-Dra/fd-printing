<x-app-layout>
<x-slot name="title">Pesanan Diterima — {{ $order->order_number }}</x-slot>

<div class="min-h-screen bg-gradient-to-b from-green-50 to-gray-50 py-12">
  <div class="max-w-xl mx-auto px-4 text-center">

    {{-- Success Icon --}}
    <div class="relative w-24 h-24 mx-auto mb-6">
      <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center animate-[ping_1s_ease-out_1]">
      </div>
      <div class="absolute inset-0 w-24 h-24 bg-green-500 rounded-full flex items-center justify-center">
        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-2">Konfirmasi Dikirim!</h1>
    <p class="text-gray-600 mb-8">
      Terima kasih! Konfirmasi pembayaran Anda untuk order
      <span class="font-bold text-primary-700">{{ $order->order_number }}</span>
      sudah kami terima. Tim kami akan memverifikasi dalam <strong>1×24 jam</strong>.
    </p>

    {{-- Order Info Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-left mb-6">
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Detail Order</h2>

      <div class="space-y-3">
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Nomor Order</span>
          <span class="font-mono font-bold text-gray-900 text-sm">{{ $order->order_number }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Tanggal Order</span>
          <span class="text-sm font-medium text-gray-800">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Status</span>
          <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-semibold">
            {{ $order->status->label() }}
          </span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Pengiriman</span>
          <span class="text-sm font-medium text-gray-800">{{ $order->delivery_type->label() }}</span>
        </div>
        @if($order->courier)
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Kurir</span>
          <span class="text-sm font-medium text-gray-800">{{ strtoupper($order->courier) }}{{ $order->courier_service ? ' — '.strtoupper($order->courier_service) : '' }}</span>
        </div>
        @endif
        <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
          <span class="text-sm font-semibold text-gray-700">Total Pembayaran</span>
          <span class="text-lg font-bold text-primary-700">{{ $order->formatted_total_amount }}</span>
        </div>
      </div>

      {{-- Items --}}
      <div class="border-t border-gray-100 mt-4 pt-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Produk yang Dipesan</p>
        <div class="space-y-2">
          @foreach($order->items as $item)
          <div class="flex justify-between text-sm">
            <span class="text-gray-700">{{ $item->product_name }}
              <span class="text-gray-400 text-xs">×{{ $item->quantity }}</span>
            </span>
            <span class="font-medium text-gray-800">{{ $item->formatted_subtotal }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Next Steps --}}
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 text-left mb-8">
      <h3 class="text-sm font-bold text-blue-800 mb-3">Apa yang terjadi selanjutnya?</h3>
      <ol class="space-y-2">
        @php $steps = [
          ['Verifikasi Pembayaran', 'Tim kami memverifikasi bukti pembayaran dalam 1×24 jam.'],
          ['Proses Cetak', 'Setelah terverifikasi, pesanan masuk antrian produksi.'],
          $order->delivery_type->value === 'delivery'
            ? ['Pengiriman', 'Produk selesai dikemas dan dikirim via '.strtoupper($order->courier ?? 'kurir pilihan').'.']
            : ['Siap Diambil', 'Kami akan menghubungi Anda saat produk siap diambil.'],
          ['Selesai', 'Produk diterima dan pesanan selesai. 🎉'],
        ]; @endphp
        @foreach($steps as $i => $step)
        <li class="flex gap-3 items-start">
          <span class="w-6 h-6 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">{{ $i + 1 }}</span>
          <div>
            <p class="text-sm font-semibold text-blue-900">{{ $step[0] }}</p>
            <p class="text-xs text-blue-600">{{ $step[1] }}</p>
          </div>
        </li>
        @endforeach
      </ol>
    </div>

    {{-- CTA Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('orders.index') }}"
         class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Lihat Semua Pesanan
      </a>
      <a href="{{ route('home') }}"
         class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Kembali ke Beranda
      </a>
    </div>

    <p class="text-xs text-gray-400 mt-8">
      Butuh bantuan? Hubungi kami di
      <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone', '')) }}"
         class="text-primary-600 hover:underline">{{ config('printing.company.phone') }}</a>
    </p>

  </div>
</div>
</x-app-layout>
