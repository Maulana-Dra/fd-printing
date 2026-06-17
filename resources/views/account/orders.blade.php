<x-app-layout>
<x-slot name="title">Pesanan Saya</x-slot>

<div class="min-h-screen bg-gray-50 py-8">
  <div class="max-w-5xl mx-auto px-4">

    {{-- ── Page Header ── --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Pesanan Saya</h1>
        <p class="text-sm text-gray-500 mt-0.5">Pantau status dan riwayat semua pesanan Anda</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('products.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white text-sm font-semibold rounded-xl hover:bg-orange-700 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Pesan Lagi
        </a>
      </div>
    </div>

    {{-- ── Status Filter Tabs ── --}}
    <div class="mb-5 flex flex-wrap gap-2">
      <a href="{{ route('orders.index') }}"
         class="px-4 py-1.5 rounded-full text-sm font-medium transition border
                {{ !$statusFilter ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-gray-600 border-gray-300 hover:border-orange-500 hover:text-orange-600' }}">
        Semua
      </a>
      @foreach($statuses as $status)
      <a href="{{ route('orders.index', ['status' => $status->value]) }}"
         class="px-4 py-1.5 rounded-full text-sm font-medium transition border
                {{ $statusFilter === $status->value ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-gray-600 border-gray-300 hover:border-orange-500 hover:text-orange-600' }}">
        {{ $status->label() }}
      </a>
      @endforeach
    </div>

    {{-- ── Empty State ── --}}
    @if($orders->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <p class="text-gray-700 font-semibold mb-1">Belum ada pesanan</p>
      <p class="text-sm text-gray-400">
        {{ $statusFilter ? 'Tidak ada pesanan dengan status ini.' : 'Mulai pesan sekarang dan temukan produk cetak terbaik!' }}
      </p>
      @if(!$statusFilter)
      <a href="{{ route('products.index') }}"
         class="mt-4 inline-block px-6 py-2 bg-orange-600 text-white text-sm font-semibold rounded-xl hover:bg-orange-700 transition">
        Mulai Belanja
      </a>
      @endif
    </div>

    {{-- ── Order List ── --}}
    @else
    <div class="space-y-4">
      @foreach($orders as $order)
      @php
        $isWaitingConfirmation = $order->status === \App\Enums\OrderStatus::PENDING_PAYMENT && !$order->needs_payment_confirmation;
        
        $statusColors = [
          'pending_payment' => 'bg-amber-100 text-amber-800',
          'paid'            => 'bg-blue-100 text-blue-800',
          'processing'      => 'bg-indigo-100 text-indigo-800',
          'ready'           => 'bg-emerald-100 text-emerald-800',
          'shipped'         => 'bg-cyan-100 text-cyan-800',
          'done'            => 'bg-green-100 text-green-800',
          'cancelled'       => 'bg-red-100 text-red-800',
        ];
        
        if ($isWaitingConfirmation) {
            $badgeClass = 'bg-blue-100 text-blue-800';
            $statusLabel = 'Menunggu Konfirmasi';
        } else {
            $badgeClass = $statusColors[$order->status->value] ?? 'bg-gray-100 text-gray-700';
            $statusLabel = $order->status->label();
        }
      @endphp
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
        {{-- Card Header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
          <div class="flex items-center gap-3">
            <span class="font-mono font-bold text-gray-900 text-sm">{{ $order->order_number }}</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
              {{ $statusLabel }}
            </span>
          </div>
          <span class="text-xs text-gray-400">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
        </div>

        {{-- Card Body --}}
        <div class="px-5 py-4">
          <div class="flex flex-col sm:flex-row sm:items-start gap-4">
            {{-- Items summary --}}
            <div class="flex-1">
              @foreach($order->items->take(2) as $item)
              <div class="flex items-start gap-2 mb-1">
                <div class="w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 shrink-0"></div>
                <span class="text-sm text-gray-700 leading-snug">
                  {{ $item->product_name }}
                  <span class="text-gray-400">×{{ $item->quantity }}</span>
                </span>
              </div>
              @endforeach
              @if($order->items->count() > 2)
              <p class="text-xs text-gray-400 ml-3.5">+{{ $order->items->count() - 2 }} produk lainnya</p>
              @endif
            </div>

            {{-- Total --}}
            <div class="text-right shrink-0">
              <p class="text-xs text-gray-400 mb-0.5">Total</p>
              <p class="text-base font-bold text-orange-600">{{ $order->formatted_total_amount }}</p>
            </div>
          </div>

          {{-- Footer action --}}
          <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap gap-2 items-center justify-between">
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              {{ $order->delivery_type->label() }}
              @if($order->courier) · {{ strtoupper($order->courier) }} @endif
            </div>
            <div class="flex gap-2">
              @if($order->needs_payment_confirmation)
              <a href="{{ route('orders.payment', $order) }}"
                 class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition">
                Bayar Sekarang
              </a>
              @endif
              <a href="{{ route('orders.show', $order->order_number) }}"
                 class="px-3 py-1.5 border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 transition">
                Lihat Detail →
              </a>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
    <div class="mt-6">
      {{ $orders->links() }}
    </div>
    @endif
    @endif

  </div>
</div>
</x-app-layout>
