<?php

namespace App\Exports;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Kelas helper untuk mengumpulkan data laporan dan mengekspor ke XLSX.
 * Menggunakan rap2hpoutre/fast-excel (sudah terpasang di project).
 */
class ReportExport
{
    public function __construct(
        protected string $dateFrom,
        protected string $dateTo,
    ) {}

    // ── Data Builders ─────────────────────────────────────────────────────────

    public function getMetrics(): array
    {
        $base = DB::table('orders')
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo);

        return [
            'total_orders'    => (clone $base)->count(),
            'total_revenue'   => (clone $base)
                ->whereNotIn('status', [OrderStatus::CANCELLED->value])
                ->sum('total_amount'),
            'done_orders'     => (clone $base)->where('status', OrderStatus::DONE->value)->count(),
            'cancelled_orders'=> (clone $base)->where('status', OrderStatus::CANCELLED->value)->count(),
        ];
    }

    public function getTopProducts(): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.created_at', '>=', $this->dateFrom)
            ->whereDate('orders.created_at', '<=', $this->dateTo)
            ->whereNotIn('orders.status', [OrderStatus::CANCELLED->value])
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
    }

    public function getPaymentMethodRecap(): Collection
    {
        return DB::table('payment_confirmations')
            ->join('payment_methods', 'payment_methods.id', '=', 'payment_confirmations.payment_method_id')
            ->whereDate('payment_confirmations.created_at', '>=', $this->dateFrom)
            ->whereDate('payment_confirmations.created_at', '<=', $this->dateTo)
            ->where('payment_confirmations.status', PaymentStatus::APPROVED->value)
            ->select(
                'payment_methods.name as method_name',
                'payment_methods.type as method_type',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(payment_confirmations.amount_paid) as total_amount'),
            )
            ->groupBy('payment_methods.id', 'payment_methods.name', 'payment_methods.type')
            ->orderByDesc('total_amount')
            ->get();
    }

    // ── Export ke XLSX ────────────────────────────────────────────────────────

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $metrics  = $this->getMetrics();
        $products = $this->getTopProducts();
        $payments = $this->getPaymentMethodRecap();
        $range    = $this->dateFrom . ' s/d ' . $this->dateTo;

        // Gabung semua data dalam satu sheet dengan separator header
        $rows = collect();

        // ── Bagian 1: Ringkasan ──────────────────────────────────────────────
        $rows->push(['Bagian' => '=== RINGKASAN PERIODE: ' . $range . ' ===', 'Kolom 2' => '', 'Kolom 3' => '', 'Kolom 4' => '']);
        $rows->push(['Bagian' => 'Total Order',       'Kolom 2' => $metrics['total_orders'],    'Kolom 3' => '', 'Kolom 4' => '']);
        $rows->push(['Bagian' => 'Total Revenue',     'Kolom 2' => 'Rp ' . number_format($metrics['total_revenue'], 0, ',', '.'), 'Kolom 3' => '', 'Kolom 4' => '']);
        $rows->push(['Bagian' => 'Order Selesai',     'Kolom 2' => $metrics['done_orders'],     'Kolom 3' => '', 'Kolom 4' => '']);
        $rows->push(['Bagian' => 'Order Dibatalkan',  'Kolom 2' => $metrics['cancelled_orders'],'Kolom 3' => '', 'Kolom 4' => '']);
        $rows->push(['Bagian' => '', 'Kolom 2' => '', 'Kolom 3' => '', 'Kolom 4' => '']);

        // ── Bagian 2: Top 10 Produk ──────────────────────────────────────────
        $rows->push(['Bagian' => '=== TOP 10 PRODUK TERLARIS ===', 'Kolom 2' => 'Total Qty', 'Kolom 3' => 'Revenue (Rp)', 'Kolom 4' => 'Jumlah Order']);
        foreach ($products as $i => $p) {
            $rows->push([
                'Bagian'  => ($i + 1) . '. ' . $p->product_name,
                'Kolom 2' => $p->total_qty,
                'Kolom 3' => number_format($p->total_revenue, 0, ',', '.'),
                'Kolom 4' => $p->order_count,
            ]);
        }
        $rows->push(['Bagian' => '', 'Kolom 2' => '', 'Kolom 3' => '', 'Kolom 4' => '']);

        // ── Bagian 3: Metode Pembayaran ──────────────────────────────────────
        $rows->push(['Bagian' => '=== REKAP METODE PEMBAYARAN ===', 'Kolom 2' => 'Tipe', 'Kolom 3' => 'Transaksi', 'Kolom 4' => 'Total Masuk (Rp)']);
        foreach ($payments as $p) {
            $rows->push([
                'Bagian'  => $p->method_name,
                'Kolom 2' => $p->method_type,
                'Kolom 3' => $p->transaction_count,
                'Kolom 4' => number_format($p->total_amount, 0, ',', '.'),
            ]);
        }

        $filename = 'laporan_fdprinting_' . str_replace('-', '', $this->dateFrom)
            . '_sd_' . str_replace('-', '', $this->dateTo) . '.xlsx';

        return (new FastExcel($rows))->download($filename);
    }
}
