<?php

namespace App\Filament\Resources;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\OrderItem;
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\ZipArchive;
use Illuminate\Support\Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Pesanan';

    protected static ?string $navigationLabel = 'Semua Order';

    protected static ?string $modelLabel = 'Order';

    protected static ?string $pluralModelLabel = 'Order';

    protected static ?int $navigationSort = 1;

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight(FontWeight::SemiBold)
                    ->copyable()
                    ->copyMessage('Disalin!'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Order $r): string => $r->user?->phone ?? '-'),

                Tables\Columns\TextColumn::make('formatted_total_amount')
                    ->label('Total')
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('total_amount', $direction)),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => OrderStatus::PENDING_PAYMENT->value,
                        'info'    => OrderStatus::PAID->value,
                        'primary' => OrderStatus::PROCESSING->value,
                        'success' => [OrderStatus::READY->value, OrderStatus::DONE->value],
                        'gray'    => OrderStatus::SHIPPED->value,
                        'danger'  => OrderStatus::CANCELLED->value,
                    ])
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label()),

                Tables\Columns\BadgeColumn::make('delivery_type')
                    ->label('Pengiriman')
                    ->colors([
                        'info'    => DeliveryType::DELIVERY->value,
                        'success' => DeliveryType::PICKUP->value,
                    ])
                    ->formatStateUsing(fn (DeliveryType $state): string => $state->label()),

                Tables\Columns\TextColumn::make('total_line_items')
                    ->label('Item')
                    ->suffix(' produk')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn (Order $r): string => $r->created_at->translatedFormat('d F Y, H:i')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Order')
                    ->options(
                        collect(OrderStatus::cases())
                            ->mapWithKeys(fn (OrderStatus $s) => [$s->value => $s->label()])
                            ->toArray()
                    )
                    ->multiple()
                    ->placeholder('Semua Status'),

                Tables\Filters\SelectFilter::make('delivery_type')
                    ->label('Tipe Pengiriman')
                    ->options([
                        DeliveryType::PICKUP->value   => DeliveryType::PICKUP->label(),
                        DeliveryType::DELIVERY->value => DeliveryType::DELIVERY->label(),
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal Order')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'],  fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null)  $indicators[] = 'Dari: ' . $data['from'];
                        if ($data['until'] ?? null) $indicators[] = 'Sampai: ' . $data['until'];
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->form(fn (Order $record): array => [
                        Forms\Components\Select::make('new_status')
                            ->label('Status Baru')
                            ->options(
                                collect($record->status->allowedTransitions())
                                    ->mapWithKeys(fn (OrderStatus $s) => [$s->value => $s->label()])
                                    ->toArray()
                            )
                            ->required()
                            ->placeholder('Pilih status tujuan'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan (Opsional)')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (Order $record, array $data, OrderService $orderService): void {
                        $newStatus = OrderStatus::from($data['new_status']);
                        try {
                            $orderService->updateStatus(
                                $record,
                                $newStatus,
                                $data['notes'] ?? null,
                                Auth::user(),
                            );
                            Notification::make()
                                ->title('Status diperbarui')
                                ->body("Order {$record->order_number} → {$newStatus->label()}")
                                ->success()->send();
                        } catch (\DomainException $e) {
                            Notification::make()
                                ->title('Transisi tidak valid')
                                ->body($e->getMessage())
                                ->danger()->send();
                        }
                    })
                    ->visible(fn (Order $r): bool => ! $r->status->isTerminal()),

                Tables\Actions\Action::make('input_resi')
                    ->label('Input Resi')
                    ->icon('heroicon-m-truck')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Nomor Resi')
                            ->required()
                            ->placeholder('Contoh: JNE1234567890'),
                    ])
                    ->action(function (Order $record, array $data, OrderService $orderService): void {
                        $record->update(['tracking_number' => $data['tracking_number']]);

                        // Otomatis transisi ke SHIPPED jika masih di READY
                        if ($record->status === OrderStatus::READY) {
                            try {
                                $orderService->updateStatus(
                                    $record,
                                    OrderStatus::SHIPPED,
                                    "Resi {$data['tracking_number']} diinput oleh admin.",
                                    Auth::user(),
                                );
                            } catch (\Throwable) {
                                // Status sudah bukan READY — update resi saja sudah cukup
                            }
                        }

                        Notification::make()
                            ->title('Resi berhasil disimpan')
                            ->body("No. resi: {$data['tracking_number']}")
                            ->success()->send();
                    })
                    ->visible(fn (Order $r): bool => in_array($r->status, [OrderStatus::READY, OrderStatus::SHIPPED])),

                Tables\Actions\Action::make('download_designs')
                    ->label('Download Desain')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->action(function (Order $record) {
                        return static::streamDesignZip($record);
                    })
                    ->visible(fn (Order $r): bool => $r->items->contains(
                        fn (OrderItem $item) => (bool) $item->design_file_path
                    )),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_update_status')
                        ->label('Update Status')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('new_status')
                                ->label('Status Baru')
                                ->options(
                                    collect(OrderStatus::cases())
                                        ->mapWithKeys(fn (OrderStatus $s) => [$s->value => $s->label()])
                                        ->toArray()
                                )
                                ->required(),
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan')
                                ->rows(2),
                        ])
                        ->action(function (Collection $records, array $data, OrderService $orderService): void {
                            $newStatus = OrderStatus::from($data['new_status']);
                            $success   = 0;
                            $failed    = 0;

                            foreach ($records as $record) {
                                try {
                                    $orderService->updateStatus($record, $newStatus, $data['notes'] ?? null, Auth::user());
                                    $success++;
                                } catch (\Throwable) {
                                    $failed++;
                                }
                            }

                            Notification::make()
                                ->title("Update selesai: {$success} berhasil" . ($failed ? ", {$failed} gagal" : ''))
                                ->color($failed > 0 ? 'warning' : 'success')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->striped()
            ->persistFiltersInSession()
            ->persistSortInSession();
    }

    // ── Infolist (View/Detail page) ───────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Order')
                    ->icon('heroicon-o-document-text')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('Nomor Order')
                            ->fontFamily('mono')
                            ->weight(FontWeight::Bold)
                            ->copyable(),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (OrderStatus $state): string => match ($state) {
                                OrderStatus::PENDING_PAYMENT => 'warning',
                                OrderStatus::PAID            => 'info',
                                OrderStatus::PROCESSING      => 'primary',
                                OrderStatus::READY           => 'success',
                                OrderStatus::SHIPPED         => 'gray',
                                OrderStatus::DONE            => 'success',
                                OrderStatus::CANCELLED       => 'danger',
                            })
                            ->formatStateUsing(fn (OrderStatus $state): string => $state->label()),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Order')
                            ->dateTime('d F Y, H:i'),

                        Infolists\Components\TextEntry::make('delivery_type')
                            ->label('Tipe Pengiriman')
                            ->badge()
                            ->formatStateUsing(fn (DeliveryType $state): string => $state->label()),

                        Infolists\Components\TextEntry::make('formatted_subtotal')
                            ->label('Subtotal'),

                        Infolists\Components\TextEntry::make('formatted_total_amount')
                            ->label('Total Bayar')
                            ->weight(FontWeight::Bold)
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('notes')
                            ->label('Catatan Customer')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Data Customer')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Nama'),

                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('user.phone')
                            ->label('No. HP')
                            ->copyable(),
                    ]),

                Infolists\Components\Section::make('Informasi Pengiriman')
                    ->icon('heroicon-o-truck')
                    ->columns(3)
                    ->visible(fn (Order $record): bool => $record->delivery_type === DeliveryType::DELIVERY)
                    ->schema([
                        Infolists\Components\TextEntry::make('recipient_name')
                            ->label('Nama Penerima'),

                        Infolists\Components\TextEntry::make('recipient_phone')
                            ->label('HP Penerima')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('courier')
                            ->label('Kurir')
                            ->formatStateUsing(fn (?string $state): string => strtoupper($state ?? '-')),

                        Infolists\Components\TextEntry::make('tracking_number')
                            ->label('Nomor Resi')
                            ->copyable()
                            ->placeholder('Belum ada resi'),

                        Infolists\Components\TextEntry::make('full_shipping_address')
                            ->label('Alamat Tujuan')
                            ->columnSpan(2),
                    ]),

                Infolists\Components\Section::make('Produk yang Dipesan')
                    ->icon('heroicon-o-printer')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')
                                    ->label('Produk')
                                    ->weight(FontWeight::SemiBold),

                                Infolists\Components\TextEntry::make('selected_options_label')
                                    ->label('Spesifikasi')
                                    ->placeholder('-'),

                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Qty')
                                    ->suffix(' pcs')
                                    ->alignCenter(),

                                Infolists\Components\TextEntry::make('formatted_unit_price')
                                    ->label('Harga Satuan'),

                                Infolists\Components\TextEntry::make('formatted_subtotal')
                                    ->label('Subtotal')
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\TextEntry::make('design_file_path')
                                    ->label('File Desain')
                                    ->placeholder('Tidak ada file')
                                    ->formatStateUsing(fn (?string $state): string => $state ? '📎 ' . basename($state) : 'Tidak ada'),

                                Infolists\Components\TextEntry::make('design_notes')
                                    ->label('Catatan Desain')
                                    ->placeholder('-'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Timeline Status')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('statusLogs')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('to_status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label()),

                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('-'),

                                Infolists\Components\TextEntry::make('changer_name')
                                    ->label('Diubah Oleh'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->dateTime('d M Y, H:i'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    // ── Form (untuk edit) ─────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Edit dilakukan via action modal, bukan form standar
            // Form ini wajib didefinisikan tapi bisa kosong
        ]);
    }

    // ── Navigation Badge ──────────────────────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $pending = Order::where('status', OrderStatus::PENDING_PAYMENT->value)->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    // ── Eager loading ─────────────────────────────────────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'items', 'statusLogs']);
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Download semua file desain dalam satu ZIP.
     */
    public static function streamDesignZip(Order $record): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $disk  = app()->isLocal() ? 'designs-local' : 'designs';
        $items = $record->items->filter(fn (OrderItem $i) => (bool) $i->design_file_path);

        $zipPath = storage_path("app/temp/order_{$record->id}_designs.zip");
        @mkdir(dirname($zipPath), 0755, true);

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($items as $item) {
            $localPath = Storage::disk($disk)->path($item->design_file_path);
            if (file_exists($localPath)) {
                $zip->addFile($localPath, basename($localPath));
            }
        }

        $zip->close();

        return response()->streamDownload(function () use ($zipPath) {
            echo file_get_contents($zipPath);
            @unlink($zipPath);
        }, "designs_{$record->order_number}.zip", [
            'Content-Type' => 'application/zip',
        ]);
    }
}
