<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Order Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user', 'items'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->fontFamily('mono')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->copyable()
                    ->copyMessage('Nomor order disalin!')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn (Order $record): string => $record->user?->email ?? '-'),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('No. HP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_line_items')
                    ->label('Item')
                    ->suffix(' produk')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('formatted_total_amount')
                    ->label('Total')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning'     => OrderStatus::PENDING_PAYMENT->value,
                        'info'        => OrderStatus::PAID->value,
                        'primary'     => OrderStatus::PROCESSING->value,
                        'success'     => [OrderStatus::READY->value, OrderStatus::DONE->value],
                        'gray'        => OrderStatus::SHIPPED->value,
                        'danger'      => OrderStatus::CANCELLED->value,
                    ])
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn (Order $record): string => $record->created_at->translatedFormat('d F Y, H:i')),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record))
                    ->openUrlInNewTab(false),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->striped();
    }
}
