<?php

namespace App\Filament\Resources\PaymentConfirmationResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentConfirmationResource;
use App\Models\PaymentConfirmation;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentConfirmations extends ListRecords
{
    protected static string $resource = PaymentConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $counts = PaymentConfirmation::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'all' => Tab::make('Semua')
                ->badge(PaymentConfirmation::count()),

            'pending' => Tab::make('Pending')
                ->badge($counts[PaymentStatus::PENDING->value] ?? 0)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentStatus::PENDING->value)),

            'approved' => Tab::make('Approved')
                ->badge($counts[PaymentStatus::APPROVED->value] ?? 0)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentStatus::APPROVED->value)),

            'rejected' => Tab::make('Rejected')
                ->badge($counts[PaymentStatus::REJECTED->value] ?? 0)
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentStatus::REJECTED->value)),
        ];
    }
}
