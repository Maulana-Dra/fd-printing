<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    // Tidak ada Create action — customer mendaftar sendiri
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $total    = User::where('is_admin', false)->count();
        $withOrder = User::where('is_admin', false)->whereHas('orders')->count();
        $newMonth  = User::where('is_admin', false)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'all' => Tab::make('Semua Customer')
                ->badge($total),

            'with_orders' => Tab::make('Pernah Pesan')
                ->badge($withOrder)
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('orders')),

            'no_orders' => Tab::make('Belum Pesan')
                ->badge($total - $withOrder)
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('orders')),

            'new_this_month' => Tab::make('Baru Bulan Ini')
                ->badge($newMonth)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                ),
        ];
    }
}
