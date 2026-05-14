<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentConfirmationResource\Pages;
use App\Jobs\SendWhatsAppNotification;
use App\Models\PaymentConfirmation;
use App\Services\OrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentConfirmationResource extends Resource
{
    protected static ?string $model = PaymentConfirmation::class;

    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Konfirmasi Pembayaran';
    protected static ?string $modelLabel      = 'Konfirmasi Pembayaran';
    protected static ?string $pluralModelLabel = 'Konfirmasi Pembayaran';
    protected static ?int    $navigationSort  = 1;

    // ── Navigation Badge ──────────────────────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = PaymentConfirmation::pending()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    // ── Eager Load ────────────────────────────────────────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['order.user', 'paymentMethod', 'confirmer']);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight(FontWeight::SemiBold)
                    ->copyable()
                    ->copyMessage('Disalin!'),

                Tables\Columns\TextColumn::make('order.user.name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (PaymentConfirmation $r): string => $r->order?->user?->phone ?? '-'),

                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Metode')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('formatted_amount_paid')
                    ->label('Jumlah Transfer')
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->sortable(query: fn (Builder $q, string $dir) => $q->orderBy('amount_paid', $dir)),

                Tables\Columns\TextColumn::make('transfer_date')
                    ->label('Tgl Transfer')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => PaymentStatus::PENDING->value,
                        'success' => PaymentStatus::APPROVED->value,
                        'danger'  => PaymentStatus::REJECTED->value,
                    ])
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label()),

                Tables\Columns\TextColumn::make('confirmer.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dikirim')
                    ->sortable()
                    ->since()
                    ->tooltip(fn (PaymentConfirmation $r): string => $r->created_at->translatedFormat('d F Y, H:i')),
            ])

            ->defaultSort('created_at', 'desc')

            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        collect(PaymentStatus::cases())
                            ->mapWithKeys(fn (PaymentStatus $s) => [$s->value => $s->label()])
                            ->toArray()
                    )
                    ->placeholder('Semua Status'),
            ])

            ->actions([
                Tables\Actions\ViewAction::make(),

                // ── Approve ───────────────────────────────────────────────────
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Approve Pembayaran')
                    ->modalDescription(fn (PaymentConfirmation $r): string =>
                        "Setujui pembayaran Rp {$r->formatted_amount_paid} dari {$r->order->user?->name} " .
                        "untuk order {$r->order->order_number}?"
                    )
                    ->modalSubmitActionLabel('Ya, Approve')
                    ->action(function (PaymentConfirmation $record, OrderService $orderService): void {
                        // 1. Tandai konfirmasi sebagai approved
                        $record->update([
                            'status'       => PaymentStatus::APPROVED,
                            'confirmed_by' => Auth::id(),
                            'confirmed_at' => now(),
                        ]);

                        // 2. Transisi order ke PAID → PROCESSING (dua langkah jika perlu)
                        $order = $record->order;
                        try {
                            // Jika order masih PENDING_PAYMENT, jadikan PAID dulu
                            if ($order->status === OrderStatus::PENDING_PAYMENT) {
                                $orderService->updateStatus(
                                    $order,
                                    OrderStatus::PAID,
                                    'Pembayaran dikonfirmasi oleh admin.',
                                    Auth::user(),
                                );
                            }

                            // Lanjut ke PROCESSING jika bisa
                            $order->refresh();
                            if ($order->status->canTransitionTo(OrderStatus::PROCESSING)) {
                                $orderService->updateStatus(
                                    $order,
                                    OrderStatus::PROCESSING,
                                    'Masuk antrian produksi setelah pembayaran diverifikasi.',
                                    Auth::user(),
                                );
                            }
                        } catch (\Throwable $e) {
                            report($e);
                            // Order status sudah berubah sebelumnya — lanjutkan saja
                        }

                        // 3. Kirim WA ke customer (async)
                        SendWhatsAppNotification::dispatch($order, 'paymentApproved');

                        Notification::make()
                            ->title('Pembayaran dikonfirmasi ✅')
                            ->body("Order {$order->order_number} diubah ke status Diproses.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (PaymentConfirmation $r): bool => $r->is_processable),

                // ── Reject ────────────────────────────────────────────────────
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Konfirmasi Pembayaran')
                    ->modalDescription('Masukkan alasan penolakan yang akan dilihat oleh customer.')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->minLength(10)
                            ->placeholder('Contoh: Nominal transfer tidak sesuai (Rp 150.000, seharusnya Rp 200.000). Silakan transfer ulang.')
                            ->helperText('Minimal 10 karakter. Alasan ini akan disimpan di sistem.'),
                    ])
                    ->modalSubmitActionLabel('Tolak Pembayaran')
                    ->action(function (PaymentConfirmation $record, array $data): void {
                        // 1. Update status konfirmasi
                        $record->update([
                            'status'           => PaymentStatus::REJECTED,
                            'confirmed_by'     => Auth::id(),
                            'confirmed_at'     => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        // 2. Notifikasi WA opsional ke customer
                        // (Pesan standar; untuk alasan custom perlu method baru di WhatsAppService)
                        // SendWhatsAppNotification::dispatch($record->order, 'paymentRejected');

                        Notification::make()
                            ->title('Pembayaran ditolak')
                            ->body("Order {$record->order->order_number} — konfirmasi pembayaran ditolak.")
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (PaymentConfirmation $r): bool => $r->is_processable),
            ])

            ->bulkActions([])
            ->striped();
    }

    // ── Infolist (View page) ──────────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Konfirmasi')
                    ->icon('heroicon-o-credit-card')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('order.order_number')
                            ->label('Nomor Order')
                            ->fontFamily('mono')
                            ->weight(FontWeight::Bold)
                            ->copyable(),

                        Infolists\Components\TextEntry::make('order.user.name')
                            ->label('Customer')
                            ->description(fn (PaymentConfirmation $r): string => $r->order?->user?->email ?? ''),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status Verifikasi')
                            ->badge()
                            ->color(fn (PaymentStatus $state): string => $state->color())
                            ->formatStateUsing(fn (PaymentStatus $state): string => $state->label()),

                        Infolists\Components\TextEntry::make('paymentMethod.name')
                            ->label('Metode Pembayaran'),

                        Infolists\Components\TextEntry::make('formatted_amount_paid')
                            ->label('Jumlah Transfer')
                            ->weight(FontWeight::Bold)
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('order.formatted_total_amount')
                            ->label('Total Order'),

                        Infolists\Components\TextEntry::make('transfer_date')
                            ->label('Tanggal Transfer')
                            ->date('d F Y'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Waktu Upload')
                            ->dateTime('d F Y, H:i'),

                        Infolists\Components\TextEntry::make('notes')
                            ->label('Catatan Customer')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->placeholder('—')
                            ->color('danger')
                            ->visible(fn (PaymentConfirmation $r): bool => $r->status === PaymentStatus::REJECTED)
                            ->columnSpanFull(),
                    ]),

                // ── Preview Bukti Bayar ───────────────────────────────────────
                Infolists\Components\Section::make('Bukti Pembayaran')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Infolists\Components\ImageEntry::make('proof_image')
                            ->label('')
                            ->disk('r2')
                            ->height(400)
                            ->extraImgAttributes(['class' => 'rounded-xl object-contain w-full']),
                    ]),

                // ── Info Verifikator ──────────────────────────────────────────
                Infolists\Components\Section::make('Informasi Verifikasi')
                    ->icon('heroicon-o-user-circle')
                    ->columns(3)
                    ->visible(fn (PaymentConfirmation $r): bool => ! $r->is_processable)
                    ->schema([
                        Infolists\Components\TextEntry::make('confirmer.name')
                            ->label('Diverifikasi Oleh'),

                        Infolists\Components\TextEntry::make('confirmed_at')
                            ->label('Waktu Verifikasi')
                            ->dateTime('d F Y, H:i'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Hasil')
                            ->badge()
                            ->color(fn (PaymentStatus $state): string => $state->color())
                            ->formatStateUsing(fn (PaymentStatus $state): string => $state->label()),
                    ]),
            ]);
    }

    // ── Form (required by Filament, detail via infolist) ─────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentConfirmations::route('/'),
            'view'  => Pages\ViewPaymentConfirmation::route('/{record}'),
        ];
    }
}
