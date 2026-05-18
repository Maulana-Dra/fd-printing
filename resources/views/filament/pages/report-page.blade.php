<x-filament-panels::page>

  {{-- ── Filter ──────────────────────────────────────────────────────────── --}}
  <x-filament::section>
    <form wire:submit="applyFilter">
      {{ $this->form }}
      <div class="flex items-center gap-3 mt-4">
        <x-filament::button type="submit" icon="heroicon-m-funnel">
          Terapkan Filter
        </x-filament::button>

        <x-filament::button
          wire:click="exportExcel"
          color="success"
          icon="heroicon-m-arrow-down-tray"
          wire:loading.attr="disabled"
        >
          <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
          <span wire:loading wire:target="exportExcel">Mengekspor...</span>
        </x-filament::button>
      </div>
    </form>
  </x-filament::section>

  {{-- ── Metric Cards ─────────────────────────────────────────────────────── --}}
  @php $m = $this->metrics @endphp
  <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">

    {{-- Total Order --}}
    <div class="col-span-1 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col gap-2">
      <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">
        <x-heroicon-o-shopping-cart class="w-4 h-4" />
        Total Order
      </div>
      <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($m['total_orders']) }}</p>
    </div>

    {{-- Revenue --}}
    <div class="col-span-1 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col gap-2 lg:col-span-2">
      <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">
        <x-heroicon-o-banknotes class="w-4 h-4" />
        Total Revenue
      </div>
      <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $this->formatRupiah($m['total_revenue']) }}</p>
      @if($m['revenue_change'] !== null)
        <p class="text-xs {{ $m['revenue_change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
          {{ $m['revenue_change'] >= 0 ? '↑' : '↓' }} {{ abs($m['revenue_change']) }}% vs bulan lalu
        </p>
      @endif
    </div>

    {{-- Order Selesai --}}
    <div class="col-span-1 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col gap-2">
      <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">
        <x-heroicon-o-check-badge class="w-4 h-4" />
        Selesai
      </div>
      <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($m['done_orders']) }}</p>
      @if($m['total_orders'] > 0)
        <p class="text-xs text-gray-400">{{ round(($m['done_orders'] / $m['total_orders']) * 100) }}% dari total</p>
      @endif
    </div>

    {{-- Dalam Produksi --}}
    <div class="col-span-1 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col gap-2">
      <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">
        <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
        Produksi
      </div>
      <p class="text-3xl font-bold text-amber-500 dark:text-amber-400">{{ number_format($m['processing_orders']) }}</p>
    </div>

    {{-- Dibatalkan --}}
    <div class="col-span-1 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col gap-2">
      <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">
        <x-heroicon-o-x-circle class="w-4 h-4" />
        Dibatalkan
      </div>
      <p class="text-3xl font-bold text-red-500 dark:text-red-400">{{ number_format($m['cancelled_orders']) }}</p>
      @if($m['total_orders'] > 0)
        <p class="text-xs text-gray-400">{{ round(($m['cancelled_orders'] / $m['total_orders']) * 100) }}% dari total</p>
      @endif
    </div>

  </div>

  {{-- ── Tabel Bawah: 2 kolom ──────────────────────────────────────────────── --}}
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

    {{-- Top 10 Produk Terlaris --}}
    <x-filament::section>
      <x-slot name="heading">
        <div class="flex items-center gap-2">
          <x-heroicon-o-trophy class="w-5 h-5 text-amber-500" />
          Top 10 Produk Terlaris
        </div>
      </x-slot>

      @php $products = $this->topProducts @endphp

      @if($products->isEmpty())
        <div class="py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
          Tidak ada data produk untuk periode ini.
        </div>
      @else
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="pb-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                <th class="pb-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Produk</th>
                <th class="pb-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                <th class="pb-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Revenue</th>
                <th class="pb-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Order</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              @foreach($products as $i => $product)
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td class="py-2.5 pr-3">
                  @if($i < 3)
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                      {{ $i === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                         ($i === 1 ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' :
                                     'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400') }}">
                      {{ $i + 1 }}
                    </span>
                  @else
                    <span class="text-gray-400 pl-1.5">{{ $i + 1 }}</span>
                  @endif
                </td>
                <td class="py-2.5 font-medium text-gray-800 dark:text-gray-200 max-w-[180px] truncate" title="{{ $product->product_name }}">
                  {{ $product->product_name }}
                </td>
                <td class="py-2.5 text-right font-semibold text-gray-700 dark:text-gray-300">
                  {{ number_format($product->total_qty) }}
                </td>
                <td class="py-2.5 text-right font-semibold text-primary-600 dark:text-primary-400">
                  {{ $this->formatRupiah($product->total_revenue) }}
                </td>
                <td class="py-2.5 text-right text-gray-500">
                  {{ number_format($product->order_count) }}
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </x-filament::section>

    {{-- Rekap per Metode Pembayaran --}}
    <x-filament::section>
      <x-slot name="heading">
        <div class="flex items-center gap-2">
          <x-heroicon-o-credit-card class="w-5 h-5 text-blue-500" />
          Rekap Metode Pembayaran
        </div>
      </x-slot>

      @php $payments = $this->paymentMethodRecap @endphp

      @if($payments->isEmpty())
        <div class="py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
          Tidak ada transaksi yang diverifikasi untuk periode ini.
        </div>
      @else
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="pb-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Metode</th>
                <th class="pb-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tipe</th>
                <th class="pb-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Transaksi</th>
                <th class="pb-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Masuk</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              @foreach($payments as $payment)
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td class="py-2.5 font-medium text-gray-800 dark:text-gray-200">
                  {{ $payment->method_name }}
                </td>
                <td class="py-2.5">
                  @php
                    $typeColors = [
                      'qris'          => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                      'bank_transfer' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                      'ewallet'       => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                    ];
                    $typeLabels = [
                      'qris'          => 'QRIS',
                      'bank_transfer' => 'Transfer Bank',
                      'ewallet'       => 'e-Wallet',
                    ];
                  @endphp
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $typeColors[$payment->method_type] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $typeLabels[$payment->method_type] ?? $payment->method_type }}
                  </span>
                </td>
                <td class="py-2.5 text-right text-gray-600 dark:text-gray-300">
                  {{ number_format($payment->transaction_count) }}x
                </td>
                <td class="py-2.5 text-right font-semibold text-green-600 dark:text-green-400">
                  {{ $this->formatRupiah($payment->total_amount) }}
                </td>
              </tr>
              @endforeach
            </tbody>
            {{-- Total row --}}
            <tfoot>
              <tr class="border-t-2 border-gray-200 dark:border-gray-700">
                <td colspan="2" class="pt-3 text-xs font-semibold text-gray-500 uppercase">Total</td>
                <td class="pt-3 text-right font-bold text-gray-800 dark:text-white">
                  {{ number_format($payments->sum('transaction_count')) }}x
                </td>
                <td class="pt-3 text-right font-bold text-green-600 dark:text-green-400">
                  {{ $this->formatRupiah($payments->sum('total_amount')) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      @endif
    </x-filament::section>

  </div>

</x-filament-panels::page>
