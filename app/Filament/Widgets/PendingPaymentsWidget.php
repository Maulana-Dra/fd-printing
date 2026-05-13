<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\PaymentConfirmation;
use App\Services\OrderService;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class PendingPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Konfirmasi Pembayaran Pending';

    // Polling otomatis setiap 60 detik
    protected static ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaymentConfirmation::query()
                    ->with(['order.user', 'order.items', 'paymentMethod'])
                    ->where('status', PaymentStatus::PENDING->value)
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('No. Order')
                    ->fontFamily('mono')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('order.user.name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (PaymentConfirmation $record): string => $record->order->user?->phone ?? '-'),

                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('formatted_amount_paid')
                    ->label('Jumlah Transfer')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('order.formatted_total_amount')
                    ->label('Total Order')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('transfer_date')
                    ->label('Tgl Transfer')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dikirim')
                    ->since()
                    ->tooltip(fn (PaymentConfirmation $r): string => $r->created_at->translatedFormat('d F Y, H:i')),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran')
                    ->modalDescription(fn (PaymentConfirmation $record): string =>
                        "Setujui pembayaran dari {$record->order->user?->name} untuk order {$record->order->order_number}?"
                    )
                    ->action(function (PaymentConfirmation $record, OrderService $orderService): void {
                        // Update status konfirmasi
                        $record->update([
                            'status'       => PaymentStatus::APPROVED,
                            'confirmed_by' => Auth::id(),
                            'confirmed_at' => now(),
                        ]);

                        // Transisi status order ke PAID
                        try {
                            $orderService->updateStatus(
                                $record->order,
                                OrderStatus::PAID,
                                'Pembayaran dikonfirmasi oleh admin.',
                                Auth::user(),
                            );
                        } catch (\Throwable $e) {
                            report($e);
                        }

                        Notification::make()
                            ->title('Pembayaran dikonfirmasi')
                            ->body("Order {$record->order->order_number} telah diubah ke status Sudah Dibayar.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pembayaran')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Contoh: Nominal tidak sesuai, bukti tidak jelas, dll.'),
                    ])
                    ->action(function (PaymentConfirmation $record, array $data): void {
                        $record->update([
                            'status'           => PaymentStatus::REJECTED,
                            'confirmed_by'     => Auth::id(),
                            'confirmed_at'     => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('Pembayaran ditolak')
                            ->body("Order {$record->order->order_number} — konfirmasi pembayaran ditolak.")
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\Action::make('view_proof')
                    ->label('Bukti')
                    ->icon('heroicon-m-photo')
                    ->color('gray')
                    ->url(fn (PaymentConfirmation $record): string => $record->proof_image_url ?? '#')
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Tidak ada konfirmasi pending')
            ->emptyStateDescription('Semua konfirmasi pembayaran sudah diproses.')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
