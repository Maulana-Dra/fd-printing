<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Tambah Kategori'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Aktif')
                ->badge(\App\Models\Category::where('is_active', true)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('is_active', true)),

            'inactive' => Tab::make('Non-aktif')
                ->badge(\App\Models\Category::where('is_active', false)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('is_active', false)),

            'all' => Tab::make('Semua')
                ->badge(\App\Models\Category::count()),
        ];
    }
}
