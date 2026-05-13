<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentConfirmation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    // Auto-refresh setiap 30 detik
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $todayOrders = Order::whereDate('created_at', today())->count();

        $pendingPayments = PaymentConfirmation::where('status', 'pending')->count();

        $inProduction = Order::where('status', OrderStatus::PROCESSING->value)->count();

        $revenueThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->sum('total_amount');

        $revenueLastMonth = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->sum('total_amount');

        // Hitung trend revenue
        $revenueDiff = $revenueThisMonth - $revenueLastMonth;
        $revenueColor = $revenueDiff >= 0 ? 'success' : 'danger';
        $revenueDescription = $revenueDiff >= 0
            ? '↑ Rp ' . Number::format($revenueDiff, 0, locale: 'id') . ' vs bulan lalu'
            : '↓ Rp ' . Number::format(abs($revenueDiff), 0, locale: 'id') . ' vs bulan lalu';

        return [
            Stat::make('Order Hari Ini', $todayOrders)
                ->description('Total order masuk hari ini')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary')
                ->chart($this->getOrderTrend()),

            Stat::make('Konfirmasi Pembayaran', $pendingPayments)
                ->description($pendingPayments > 0 ? 'Perlu segera diverifikasi!' : 'Tidak ada yang pending')
                ->descriptionIcon($pendingPayments > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pendingPayments > 0 ? 'danger' : 'success'),

            Stat::make('Dalam Produksi', $inProduction)
                ->description('Order sedang diproses')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('warning'),

            Stat::make('Revenue Bulan Ini', 'Rp ' . Number::format($revenueThisMonth, 0, locale: 'id'))
                ->description($revenueDescription)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($revenueColor),
        ];
    }

    /**
     * Data chart order 7 hari terakhir untuk sparkline.
     */
    private function getOrderTrend(): array
    {
        return collect(range(6, 0))
            ->map(fn ($days) => Order::whereDate('created_at', now()->subDays($days))->count())
            ->values()
            ->toArray();
    }
}
