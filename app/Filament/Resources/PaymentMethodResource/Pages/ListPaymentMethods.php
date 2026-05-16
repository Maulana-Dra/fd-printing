<?php

namespace App\Filament\Resources\PaymentMethodResource\Pages;

use App\Enums\PaymentMethodType;
use App\Filament\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentMethods extends ListRecords
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Tambah Metode'),
        ];
    }

    public function getTabs(): array
    {
        $counts = PaymentMethod::query()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        return [
            'all' => Tab::make('Semua')
                ->badge(PaymentMethod::count()),

            'qris' => Tab::make('QRIS')
                ->badge($counts[PaymentMethodType::QRIS->value] ?? 0)
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('type', PaymentMethodType::QRIS->value)),

            'bank_transfer' => Tab::make('Transfer Bank')
                ->badge($counts[PaymentMethodType::BANK_TRANSFER->value] ?? 0)
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('type', PaymentMethodType::BANK_TRANSFER->value)),

            'ewallet' => Tab::make('e-Wallet')
                ->badge($counts[PaymentMethodType::EWALLET->value] ?? 0)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('type', PaymentMethodType::EWALLET->value)),
        ];
    }
}
