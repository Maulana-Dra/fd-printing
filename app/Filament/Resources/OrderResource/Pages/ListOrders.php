<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\OrderStatus;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada tombol "Create" — order dibuat oleh customer
        ];
    }

    /**
     * Tab filter cepat berdasarkan status paling umum.
     */
    public function getTabs(): array
    {
        $counts = \App\Models\Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'all' => Tab::make('Semua')
                ->badge(\App\Models\Order::count()),

            'pending_payment' => Tab::make('Menunggu Bayar')
                ->badge($counts[OrderStatus::PENDING_PAYMENT->value] ?? 0)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::PENDING_PAYMENT->value)),

            'paid' => Tab::make('Sudah Dibayar')
                ->badge($counts[OrderStatus::PAID->value] ?? 0)
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::PAID->value)),

            'processing' => Tab::make('Produksi')
                ->badge($counts[OrderStatus::PROCESSING->value] ?? 0)
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::PROCESSING->value)),

            'ready' => Tab::make('Siap Kirim')
                ->badge($counts[OrderStatus::READY->value] ?? 0)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::READY->value)),

            'shipped' => Tab::make('Dikirim')
                ->badge($counts[OrderStatus::SHIPPED->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::SHIPPED->value)),

            'done' => Tab::make('Selesai')
                ->badge($counts[OrderStatus::DONE->value] ?? 0)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::DONE->value)),

            'cancelled' => Tab::make('Dibatalkan')
                ->badge($counts[OrderStatus::CANCELLED->value] ?? 0)
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::CANCELLED->value)),
        ];
    }
}
