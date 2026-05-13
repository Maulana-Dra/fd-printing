<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Services\OrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('update_status')
                ->label('Update Status')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->form(fn (): array => [
                    \Filament\Forms\Components\Select::make('new_status')
                        ->label('Status Baru')
                        ->options(
                            collect($this->record->status->allowedTransitions())
                                ->mapWithKeys(fn (OrderStatus $s) => [$s->value => $s->label()])
                                ->toArray()
                        )
                        ->required()
                        ->placeholder('Pilih status tujuan'),
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(2),
                ])
                ->action(function (array $data, OrderService $orderService): void {
                    $newStatus = OrderStatus::from($data['new_status']);
                    try {
                        $orderService->updateStatus(
                            $this->record,
                            $newStatus,
                            $data['notes'] ?? null,
                            Auth::user(),
                        );
                        $this->record->refresh();
                        Notification::make()
                            ->title('Status diperbarui')
                            ->body("{$this->record->order_number} → {$newStatus->label()}")
                            ->success()->send();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('Transisi tidak valid')
                            ->body($e->getMessage())
                            ->danger()->send();
                    }
                })
                ->visible(fn (): bool => ! $this->record->status->isTerminal()),

            Actions\Action::make('input_resi')
                ->label('Input Resi')
                ->icon('heroicon-m-truck')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\TextInput::make('tracking_number')
                        ->label('Nomor Resi')
                        ->default(fn () => $this->record->tracking_number)
                        ->required(),
                ])
                ->action(function (array $data, OrderService $orderService): void {
                    $this->record->update(['tracking_number' => $data['tracking_number']]);
                    if ($this->record->status === OrderStatus::READY) {
                        try {
                            $orderService->updateStatus($this->record, OrderStatus::SHIPPED, "Resi {$data['tracking_number']} diinput.", Auth::user());
                        } catch (\Throwable) {}
                    }
                    $this->record->refresh();
                    Notification::make()->title('Resi disimpan')->success()->send();
                })
                ->visible(fn (): bool => in_array($this->record->status, [OrderStatus::READY, OrderStatus::SHIPPED])),

            Actions\Action::make('download_designs')
                ->label('Download Desain')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => OrderResource::streamDesignZip($this->record))
                ->visible(fn (): bool => $this->record->items->contains(
                    fn ($item) => (bool) $item->design_file_path
                )),

            Actions\Action::make('back')
                ->label('← Daftar Order')
                ->url(OrderResource::getUrl())
                ->color('gray'),
        ];
    }
}
