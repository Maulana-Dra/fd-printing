<x-app-layout>
<x-slot name="title">Detail Pesanan {{ $order->order_number }}</x-slot>

<div class="min-h-screen bg-gray-50 py-8">
  <div class="max-w-4xl mx-auto px-4">

    {{-- ── Breadcrumb ── --}}
    <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500">
      <a href="{{ route('orders.index') }}" class="hover:text-primary-600 transition">Pesanan Saya</a>
      <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="font-mono font-semibold text-gray-800">{{ $order->order_number }}</span>
    </nav>

    @php
      $statusColors = [
        'pending_payment' => ['badge' => 'bg-amber-100 text-amber-800',   'dot' => 'bg-amber-400',   'ring' => 'ring-amber-300'],
        'paid'            => ['badge' => 'bg-blue-100 text-blue-800',     'dot' => 'bg-blue-400',    'ring' => 'ring-blue-300'],
        'processing'      => ['badge' => 'bg-indigo-100 text-indigo-800', 'dot' => 'bg-indigo-500',  'ring' => 'ring-indigo-300'],
        'ready'           => ['badge' => 'bg-emerald-100 text-emerald-800','dot'=> 'bg-emerald-500', 'ring' => 'ring-emerald-300'],
        'shipped'         => ['badge' => 'bg-cyan-100 text-cyan-800',     'dot' => 'bg-cyan-500',    'ring' => 'ring-cyan-300'],
        'done'            => ['badge' => 'bg-green-100 text-green-800',   'dot' => 'bg-green-500',   'ring' => 'ring-green-300'],
        'cancelled'       => ['badge' => 'bg-red-100 text-red-800',       'dot' => 'bg-red-400',     'ring' => 'ring-red-300'],
      ];
      $sc = $statusColors[$order->status->value] ?? ['badge' => 'bg-gray-100 text-gray-700', 'dot' => 'bg-gray-400', 'ring' => 'ring-gray-300'];
    @endphp

    {{-- ── Main Grid ── --}}
    <div class="flex flex-col lg:flex-row gap-6">

      {{-- ── Left Column ── --}}
      <div class="flex-1 space-y-5">

        {{-- Order Header Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-xs text-gray-400 mb-1">Nomor Pesanan</p>
              <p class="font-mono font-bold text-gray-900 text-lg">{{ $order->order_number }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $sc['badge'] }}">
              {{ $order->status->label() }}
            </span>
          </div>

          {{-- CTA if still needs payment --}}
          @if($order->needs_payment_confirmation)
          <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between gap-3">
            <p class="text-sm text-amber-800">Pesanan menunggu konfirmasi pembayaran Anda.</p>
            <a href="{{ route('orders.payment', $order) }}"
               class="shrink-0 px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition">
              Bayar Sekarang
            </a>
          </div>
          @endif
        </div>

        {{-- ══ STATUS TIMELINE ══ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-5">Timeline Status</h2>

          @php
            $statusOrder = ['pending_payment','paid','processing','ready','shipped','done'];
            $isCancelled = $order->status->value === 'cancelled';
            $currentIdx  = array_search($order->status->value, $statusOrder);
          @endphp

          @if($isCancelled)
          {{-- Cancelled State --}}
          <div class="flex items-center gap-3 p-3 bg-red-50 rounded-xl border border-red-200">
            <div class="w-9 h-9 rounded-full bg-red-500 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-red-800">Pesanan Dibatalkan</p>
              @if($order->cancelled_reason)
              <p class="text-xs text-red-600 mt-0.5">{{ $order->cancelled_reason }}</p>
              @endif
            </div>
          </div>
          @endif

          {{-- Timeline from status_logs --}}
          <div class="relative">
            @foreach($order->statusLogs as $i => $log)
            @php
              $isLast = $i === $order->statusLogs->count() - 1;
              $logSc  = $statusColors[$log->to_status->value] ?? ['dot' => 'bg-gray-400'];
            @endphp
            <div class="flex gap-4 {{ !$isLast ? 'mb-0' : '' }}">
              {{-- Line + Dot --}}
              <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full {{ $logSc['dot'] }} flex items-center justify-center shrink-0 ring-4 {{ $isLast ? $sc['ring'] : 'ring-gray-100' }}">
                  @if($isLast)
                  <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  @else
                  <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  @endif
                </div>
                @if(!$isLast)
                <div class="w-0.5 bg-gray-200 flex-1 my-1 min-h-[1.5rem]"></div>
                @endif
              </div>

              {{-- Content --}}
              <div class="pb-4 flex-1 min-w-0">
                <div class="flex flex-wrap items-baseline gap-x-2">
                  <p class="text-sm font-semibold text-gray-800">{{ $log->to_status->label() }}</p>
                  <p class="text-xs text-gray-400">{{ $log->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                </div>
                @if($log->notes)
                <p class="text-xs text-gray-500 mt-0.5">{{ $log->notes }}</p>
                @endif
                @if($log->changed_by)
                <p class="text-xs text-gray-400 mt-0.5">oleh: {{ $log->changer_name }}</p>
                @endif
              </div>
            </div>
            @endforeach

            {{-- Placeholder steps belum tercapai (jika tidak cancelled) --}}
            @if(!$isCancelled && $currentIdx !== false)
            @php $pendingStatuses = array_slice($statusOrder, $currentIdx + 1); @endphp
            @foreach($pendingStatuses as $j => $pendingValue)
            @php
              $pendingStatus = \App\Enums\OrderStatus::from($pendingValue);
              $isLastPending = $j === count($pendingStatuses) - 1;
            @endphp
            <div class="flex gap-4">
              <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center shrink-0 ring-4 ring-gray-50">
                  <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                </div>
                @if(!$isLastPending)
                <div class="w-0.5 bg-gray-100 flex-1 my-1 min-h-[1.5rem]"></div>
                @endif
              </div>
              <div class="pb-4 flex-1">
                <p class="text-sm text-gray-400">{{ $pendingStatus->label() }}</p>
              </div>
            </div>
            @endforeach
            @endif
          </div>
        </div>

        {{-- ══ DAFTAR PRODUK ══ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Produk Dipesan</h2>
          <div class="space-y-4">
            @foreach($order->items as $item)
            <div class="flex gap-3 items-start pb-4 border-b border-gray-100 last:border-0 last:pb-0">
              {{-- Icon placeholder --}}
              <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900">{{ $item->product_name }}</p>
                @if($item->selected_options_label !== '-')
                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $item->selected_options_label }}</p>
                @endif
                @if($item->design_notes)
                <p class="text-xs text-blue-600 mt-0.5">Catatan: {{ $item->design_notes }}</p>
                @endif
              </div>
              <div class="text-right shrink-0">
                <p class="text-xs text-gray-400">×{{ $item->quantity }} × {{ $item->formatted_unit_price }}</p>
                <p class="text-sm font-bold text-gray-900">{{ $item->formatted_subtotal }}</p>
              </div>
            </div>
            @endforeach
          </div>

          {{-- Totals --}}
          <div class="mt-4 pt-4 border-t border-gray-200 space-y-1.5">
            <div class="flex justify-between text-sm text-gray-600">
              <span>Subtotal</span>
              <span>{{ $order->formatted_subtotal }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
              <span>Ongkos Kirim</span>
              <span>{{ $order->shipping_cost > 0 ? $order->formatted_shipping_cost : 'Gratis / Ambil Sendiri' }}</span>
            </div>
            <div class="flex justify-between font-bold text-base border-t border-gray-200 pt-2 mt-2">
              <span>Total</span>
              <span class="text-primary-700">{{ $order->formatted_total_amount }}</span>
            </div>
          </div>
        </div>

        {{-- ══ BUKTI PEMBAYARAN ══ --}}
        @if($order->latestPaymentConfirmation)
        @php $conf = $order->latestPaymentConfirmation; @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Konfirmasi Pembayaran</h2>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-xs text-gray-400">Metode</p>
              <p class="font-medium text-gray-800">{{ $conf->paymentMethod?->name ?? '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400">Jumlah Transfer</p>
              <p class="font-medium text-gray-800">{{ $conf->formatted_amount_paid }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400">Tanggal Transfer</p>
              <p class="font-medium text-gray-800">{{ $conf->formatted_transfer_date }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400">Status Verifikasi</p>
              @php
                $confColors = ['pending' => 'text-amber-700 bg-amber-50', 'approved' => 'text-green-700 bg-green-50', 'rejected' => 'text-red-700 bg-red-50'];
                $confClass  = $confColors[$conf->status->value] ?? 'text-gray-700';
              @endphp
              <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $confClass }}">
                {{ $conf->status->label() }}
              </span>
            </div>
            @if($conf->notes)
            <div class="col-span-2">
              <p class="text-xs text-gray-400">Catatan</p>
              <p class="text-sm text-gray-700">{{ $conf->notes }}</p>
            </div>
            @endif
          </div>
        </div>
        @endif

      </div>

      {{-- ── Right Sidebar ── --}}
      <div class="lg:w-72 shrink-0 space-y-5">

        {{-- Delivery Info --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">Info Pengiriman</h2>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Tipe</span>
              <span class="font-medium">{{ $order->delivery_type->label() }}</span>
            </div>
            @if($order->courier)
            <div class="flex justify-between">
              <span class="text-gray-500">Kurir</span>
              <span class="font-medium">{{ strtoupper($order->courier) }}{{ $order->courier_service ? ' - '.$order->courier_service : '' }}</span>
            </div>
            @endif
            @if($order->tracking_number)
            <div class="flex justify-between">
              <span class="text-gray-500">No. Resi</span>
              <span class="font-mono font-semibold text-primary-700">{{ $order->tracking_number }}</span>
            </div>
            @endif
          </div>

          @if($order->full_shipping_address)
          <div class="mt-3 pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-400 mb-1">Alamat Tujuan</p>
            <p class="text-sm font-medium text-gray-800">{{ $order->recipient_name }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $order->recipient_phone }}</p>
            <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $order->full_shipping_address }}</p>
          </div>
          @else
          <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
            Ambil di toko kami.
          </div>
          @endif
        </div>

        {{-- Notes --}}
        @if($order->notes)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Catatan Order</h2>
          <p class="text-sm text-gray-700">{{ $order->notes }}</p>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="space-y-2">
          @if($order->needs_payment_confirmation)
          <a href="{{ route('orders.payment', $order) }}"
             class="block w-full text-center py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition">
            Upload Bukti Pembayaran
          </a>
          @endif
          <a href="{{ route('orders.index') }}"
             class="block w-full text-center py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition text-sm">
            ← Kembali ke Daftar Pesanan
          </a>
          <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone', '')) }}?text={{ urlencode('Halo, saya ingin tanya tentang pesanan ' . $order->order_number) }}"
             target="_blank" rel="noopener"
             class="block w-full text-center py-2.5 bg-green-500 hover:bg-green-600 text-white font-medium rounded-xl transition text-sm flex items-center justify-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Tanya via WhatsApp
          </a>
        </div>

      </div>
    </div>

  </div>
</div>
</x-app-layout>
