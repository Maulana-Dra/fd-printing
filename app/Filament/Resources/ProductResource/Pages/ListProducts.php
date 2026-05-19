<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Tambah Produk'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Aktif')
                ->badge(\App\Models\Product::where('is_active', true)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),

            'inactive' => Tab::make('Non-aktif')
                ->badge(\App\Models\Product::where('is_active', false)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),

            'all' => Tab::make('Semua')
                ->badge(\App\Models\Product::count()),

            'trashed' => Tab::make('Dihapus')
                ->badge(\App\Models\Product::onlyTrashed()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
