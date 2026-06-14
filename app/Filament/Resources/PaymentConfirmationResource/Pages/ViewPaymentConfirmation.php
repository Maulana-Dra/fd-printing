<?php

namespace App\Filament\Resources\PaymentConfirmationResource\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentConfirmationResource;
use App\Jobs\SendWhatsAppNotification;
use App\Models\PaymentConfirmation;
use App\Services\OrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewPaymentConfirmation extends ViewRecord
{
    protected static string $resource = PaymentConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve Pembayaran')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Approve')
                ->modalDescription(fn (): string =>
                    "Setujui pembayaran {$this->record->formatted_amount_paid} dari " .
                    "{$this->record->order->user?->name} untuk order {$this->record->order->order_number}?"
                )
                ->modalSubmitActionLabel('Ya, Approve')
                ->action(function (OrderService $orderService): void {
                    $record = $this->record;

                    $record->update([
                        'status'       => PaymentStatus::APPROVED,
                        'confirmed_by' => Auth::id(),
                        'confirmed_at' => now(),
                    ]);

                    $order = $record->order;
                    try {
                        if ($order->status === OrderStatus::PENDING_PAYMENT) {
                            $orderService->updateStatus($order, OrderStatus::PAID, 'Pembayaran dikonfirmasi.', Auth::user());
                        }
                        $order->refresh();
                        if ($order->status->canTransitionTo(OrderStatus::PROCESSING)) {
                            $orderService->updateStatus($order, OrderStatus::PROCESSING, 'Masuk antrian produksi.', Auth::user());
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }

                    SendWhatsAppNotification::dispatch($order, 'paymentApproved');

                    $this->record->refresh();

                    Notification::make()
                        ->title('Pembayaran dikonfirmasi ✅')
                        ->body("Order {$order->order_number} diubah ke status Diproses.")
                        ->success()->send();
                })
                ->visible(fn (): bool => $this->record->is_processable),

            Actions\Action::make('reject')
                ->label('Tolak Pembayaran')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->rows(3)
                        ->minLength(10)
                        ->placeholder('Contoh: Nominal transfer tidak sesuai.'),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status'           => PaymentStatus::REJECTED,
                        'confirmed_by'     => Auth::id(),
                        'confirmed_at'     => now(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    $this->record->refresh();

                    Notification::make()
                        ->title('Pembayaran ditolak')
                        ->warning()->send();
                })
                ->visible(fn (): bool => $this->record->is_processable),

            Actions\Action::make('back')
                ->label('← Daftar Konfirmasi')
                ->url(PaymentConfirmationResource::getUrl())
                ->color('gray'),
        ];
    }
}
