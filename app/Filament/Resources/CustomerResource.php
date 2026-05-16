<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Customer';
    protected static ?string $modelLabel      = 'Customer';
    protected static ?string $pluralModelLabel = 'Customer';
    protected static ?int    $navigationSort  = 1;

    // ── Hanya tampilkan user yang bukan admin ─────────────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_admin', false)
            ->withCount('orders')
            ->withSum(
                ['orders' => fn ($q) => $q->whereNotIn('status', [OrderStatus::CANCELLED->value])],
                'total_amount'
            );
    }

    // ── Form (disabled — read-only resource) ──────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->disk('r2')
                    ->defaultImageUrl(fn (User $r): string =>
                        'https://ui-avatars.com/api/?name=' . urlencode($r->name) . '&background=f97316&color=fff&size=80'
                    ),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (User $r): string => $r->email),

                Tables\Columns\TextColumn::make('phone')
                    ->label('No. HP')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Total Order')
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->suffix(' order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('orders_sum_total_amount')
                    ->label('Total Belanja')
                    ->formatStateUsing(fn ($state): string =>
                        $state ? 'Rp ' . number_format((float) $state, 0, ',', '.') : 'Rp 0'
                    )
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->since()
                    ->placeholder('Belum pernah login')
                    ->tooltip(fn (User $r): ?string => $r->last_login_at?->translatedFormat('d F Y, H:i'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->date('d M Y')
                    ->sortable()
                    ->description(fn (User $r): string => $r->created_at->diffForHumans()),
            ])

            ->defaultSort('created_at', 'desc')

            ->filters([
                Tables\Filters\Filter::make('has_orders')
                    ->label('Pernah Pesan')
                    ->query(fn (Builder $q) => $q->whereHas('orders')),

                Tables\Filters\Filter::make('registered_this_month')
                    ->label('Daftar Bulan Ini')
                    ->query(fn (Builder $q) => $q->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)),
            ])

            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
            ])

            ->bulkActions([]) // Tidak ada bulk action untuk customer

            ->striped();
    }

    // ── Infolist (Detail Customer) ─────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Infolists\Components\Section::make('Profil Customer')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    Infolists\Components\ImageEntry::make('avatar')
                        ->label('Avatar')
                        ->disk('r2')
                        ->circular()
                        ->height(80)
                        ->defaultImageUrl(fn (User $r): string =>
                            'https://ui-avatars.com/api/?name=' . urlencode($r->name) . '&background=f97316&color=fff&size=160'
                        ),

                    Infolists\Components\TextEntry::make('name')
                        ->label('Nama Lengkap')
                        ->weight(FontWeight::Bold)
                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                    Infolists\Components\TextEntry::make('email')
                        ->label('Email')
                        ->copyable()
                        ->icon('heroicon-m-envelope'),

                    Infolists\Components\TextEntry::make('phone')
                        ->label('No. HP')
                        ->copyable()
                        ->placeholder('—')
                        ->icon('heroicon-m-phone'),

                    Infolists\Components\TextEntry::make('address')
                        ->label('Alamat')
                        ->placeholder('—')
                        ->columnSpan(2),

                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Bergabung Sejak')
                        ->dateTime('d F Y'),

                    Infolists\Components\TextEntry::make('last_login_at')
                        ->label('Login Terakhir')
                        ->dateTime('d F Y, H:i')
                        ->placeholder('Belum pernah login'),

                    Infolists\Components\TextEntry::make('email_verified_at')
                        ->label('Verifikasi Email')
                        ->dateTime('d F Y, H:i')
                        ->placeholder('Belum diverifikasi'),
                ]),

            Infolists\Components\Section::make('Statistik Belanja')
                ->icon('heroicon-o-chart-bar')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('orders_count')
                        ->label('Total Order')
                        ->state(fn (User $r): int => $r->orders()->count())
                        ->suffix(' order')
                        ->badge()
                        ->color('primary'),

                    Infolists\Components\TextEntry::make('completed_orders')
                        ->label('Order Selesai')
                        ->state(fn (User $r): int => $r->orders()->where('status', OrderStatus::DONE->value)->count())
                        ->suffix(' order')
                        ->badge()
                        ->color('success'),

                    Infolists\Components\TextEntry::make('pending_orders')
                        ->label('Order Pending')
                        ->state(fn (User $r): int =>
                            $r->orders()->whereIn('status', [
                                OrderStatus::PENDING_PAYMENT->value,
                                OrderStatus::PAID->value,
                                OrderStatus::PROCESSING->value,
                            ])->count()
                        )
                        ->suffix(' order')
                        ->badge()
                        ->color('warning'),

                    Infolists\Components\TextEntry::make('total_spent')
                        ->label('Total Pengeluaran')
                        ->state(fn (User $r): string =>
                            'Rp ' . number_format(
                                (float) $r->orders()
                                    ->whereNotIn('status', [OrderStatus::CANCELLED->value])
                                    ->sum('total_amount'),
                                0, ',', '.'
                            )
                        )
                        ->color('success')
                        ->weight(FontWeight::Bold),
                ]),

            Infolists\Components\Section::make('Riwayat Pesanan')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('orders')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('order_number')
                                ->label('No. Order')
                                ->fontFamily('mono')
                                ->weight(FontWeight::SemiBold)
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

                            Infolists\Components\TextEntry::make('formatted_total_amount')
                                ->label('Total')
                                ->weight(FontWeight::Bold)
                                ->color('primary'),

                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Tanggal')
                                ->date('d M Y'),
                        ])
                        ->columns(4)
                        ->columnSpanFull(),
                ]),

        ]);
    }

    // ── Navigation Badge ──────────────────────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $newThisMonth = User::where('is_admin', false)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $newThisMonth > 0 ? '+' . $newThisMonth : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'view'  => Pages\ViewCustomer::route('/{record}'),
        ];
    }
}
