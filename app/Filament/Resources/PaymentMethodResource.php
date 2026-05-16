<?php

namespace App\Filament\Resources;

use App\Enums\PaymentMethodType;
use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Metode Pembayaran';
    protected static ?string $modelLabel      = 'Metode Pembayaran';
    protected static ?string $pluralModelLabel = 'Metode Pembayaran';
    protected static ?int    $navigationSort  = 2;

    // ── Form ──────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Group::make()->schema([

                // ── Identitas Metode ─────────────────────────────────────────
                Forms\Components\Section::make('Identitas Metode Pembayaran')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe Metode')
                            ->options(
                                collect(PaymentMethodType::cases())
                                    ->mapWithKeys(fn (PaymentMethodType $t) => [$t->value => $t->label()])
                                    ->toArray()
                            )
                            ->required()
                            ->native(false)
                            ->live()  // Trigger reactive field visibility
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                // Reset field QR & nomor rekening saat tipe berubah
                                if ($state === PaymentMethodType::QRIS->value) {
                                    $set('account_number', null);
                                }
                                if ($state !== PaymentMethodType::QRIS->value) {
                                    $set('qr_image', null);
                                }
                            })
                            ->helperText('Pilih tipe untuk menampilkan field yang relevan.'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Tampilan')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: BCA, GoPay, QRIS Toko')
                            ->helperText('Nama yang ditampilkan ke customer saat checkout.'),

                        // bank_name — tampil untuk bank_transfer & ewallet
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Nama Bank / Provider')
                            ->maxLength(60)
                            ->nullable()
                            ->placeholder('Contoh: Bank Central Asia, GoPay, OVO')
                            ->visible(fn (Get $get): bool => in_array(
                                $get('type'),
                                [PaymentMethodType::BANK_TRANSFER->value, PaymentMethodType::EWALLET->value]
                            ))
                            ->helperText('Contoh: BCA, Mandiri, GoPay, OVO.'),

                        // account_number — sembunyikan untuk QRIS
                        Forms\Components\TextInput::make('account_number')
                            ->label('Nomor Rekening / Akun')
                            ->maxLength(50)
                            ->nullable()
                            ->placeholder('Contoh: 1234567890 atau 08xxx')
                            ->visible(fn (Get $get): bool => $get('type') !== PaymentMethodType::QRIS->value)
                            ->helperText('Nomor rekening bank atau nomor HP e-wallet.'),

                        Forms\Components\TextInput::make('account_name')
                            ->label('Atas Nama')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: FD PRINTING')
                            ->helperText('Nama pemilik rekening / akun.'),
                    ])
                    ->columns(2),

                // ── QR Code — hanya QRIS ─────────────────────────────────────
                Forms\Components\Section::make('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->description('Upload gambar QR code yang akan ditampilkan ke customer.')
                    ->visible(fn (Get $get): bool => $get('type') === PaymentMethodType::QRIS->value)
                    ->schema([
                        Forms\Components\FileUpload::make('qr_image')
                            ->label('Gambar QR Code')
                            ->image()
                            ->disk('r2')
                            ->directory('payment-qr')
                            ->visibility('public')
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('600')
                            ->imageResizeTargetHeight('600')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Format JPG/PNG/WebP, maks. 2MB. Disarankan ukuran minimal 400×400px.'),
                    ]),

                // ── Deskripsi ─────────────────────────────────────────────────
                Forms\Components\Section::make('Informasi Tambahan')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi / Instruksi')
                            ->rows(3)
                            ->maxLength(500)
                            ->nullable()
                            ->placeholder('Contoh: Transfer ke rekening ini kemudian upload bukti pembayaran.')
                            ->helperText('Instruksi singkat yang ditampilkan ke customer. Maks. 500 karakter.'),
                    ]),

            ])->columnSpan(2),

            // ── Kolom Kanan: Pengaturan ──────────────────────────────────────
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Metode non-aktif tidak tampil di halaman pembayaran.')
                            ->default(true)
                            ->onColor('success'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lebih kecil = tampil lebih atas.'),
                    ]),

                // Preview card dinamis berdasarkan tipe yang dipilih
                Forms\Components\Section::make('Panduan Field')
                    ->icon('heroicon-o-light-bulb')
                    ->schema([
                        Forms\Components\Placeholder::make('guide_qris')
                            ->label('')
                            ->content('📱 **QRIS**: Isi nama + atas nama + upload gambar QR code.')
                            ->visible(fn (Get $get): bool => $get('type') === PaymentMethodType::QRIS->value),

                        Forms\Components\Placeholder::make('guide_bank')
                            ->label('')
                            ->content('🏦 **Transfer Bank**: Isi nama bank, nomor rekening, dan atas nama.')
                            ->visible(fn (Get $get): bool => $get('type') === PaymentMethodType::BANK_TRANSFER->value),

                        Forms\Components\Placeholder::make('guide_ewallet')
                            ->label('')
                            ->content('📲 **e-Wallet**: Isi provider (GoPay/OVO), nomor HP, dan atas nama.')
                            ->visible(fn (Get $get): bool => $get('type') === PaymentMethodType::EWALLET->value),

                        Forms\Components\Placeholder::make('guide_default')
                            ->label('')
                            ->content('← Pilih tipe metode pembayaran terlebih dahulu.')
                            ->visible(fn (Get $get): bool => empty($get('type'))),
                    ]),

            ])->columnSpan(1),

        ])->columns(3);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Thumbnail QR untuk QRIS, ikon untuk yang lain
                Tables\Columns\ImageColumn::make('qr_image')
                    ->label('')
                    ->disk('r2')
                    ->square()
                    ->size(48)
                    ->defaultImageUrl(fn (PaymentMethod $r): string =>
                        'https://placehold.co/48x48/f97316/fff?text=' . urlencode(strtoupper(substr($r->name, 0, 2)))
                    )
                    ->visible(fn (): bool => true),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'primary' => PaymentMethodType::QRIS->value,
                        'info'    => PaymentMethodType::BANK_TRANSFER->value,
                        'success' => PaymentMethodType::EWALLET->value,
                    ])
                    ->formatStateUsing(fn (PaymentMethodType $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (PaymentMethod $r): string => $r->display_label),

                Tables\Columns\TextColumn::make('account_number')
                    ->label('No. Rekening / Akun')
                    ->placeholder('—')
                    ->copyable()
                    ->copyMessage('Nomor disalin!'),

                Tables\Columns\TextColumn::make('account_name')
                    ->label('Atas Nama'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
            ])

            ->defaultSort('sort_order')

            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options(
                        collect(PaymentMethodType::cases())
                            ->mapWithKeys(fn (PaymentMethodType $t) => [$t->value => $t->label()])
                            ->toArray()
                    )
                    ->placeholder('Semua Tipe'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non-aktif')
                    ->placeholder('Semua'),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (PaymentMethod $r): string => $r->is_active ? 'Non-aktifkan' : 'Aktifkan')
                    ->icon(fn (PaymentMethod $r): string => $r->is_active ? 'heroicon-m-eye-slash' : 'heroicon-m-eye')
                    ->color(fn (PaymentMethod $r): string => $r->is_active ? 'warning' : 'success')
                    ->action(fn (PaymentMethod $record) => $record->update(['is_active' => ! $record->is_active]))
                    ->requiresConfirmation(fn (PaymentMethod $r): bool => $r->is_active)
                    ->modalDescription(fn (PaymentMethod $r): string =>
                        "Non-aktifkan \"{$r->name}\"? Metode ini tidak akan tampil di halaman pembayaran customer."
                    ),

                Tables\Actions\DeleteAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-m-eye')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Non-aktifkan')
                        ->icon('heroicon-m-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])

            ->striped();
    }

    // ── Infolist ──────────────────────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Metode Pembayaran')
                ->icon('heroicon-o-credit-card')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('type')
                        ->label('Tipe')
                        ->badge()
                        ->formatStateUsing(fn (PaymentMethodType $state): string => $state->label()),

                    Infolists\Components\TextEntry::make('name')
                        ->label('Nama')
                        ->weight(FontWeight::Bold),

                    Infolists\Components\IconEntry::make('is_active')
                        ->label('Status')
                        ->boolean(),

                    Infolists\Components\TextEntry::make('bank_name')
                        ->label('Bank / Provider')
                        ->placeholder('—'),

                    Infolists\Components\TextEntry::make('account_number')
                        ->label('No. Rekening / Akun')
                        ->placeholder('—')
                        ->copyable(),

                    Infolists\Components\TextEntry::make('account_name')
                        ->label('Atas Nama'),

                    Infolists\Components\TextEntry::make('description')
                        ->label('Deskripsi / Instruksi')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),

            Infolists\Components\Section::make('QR Code')
                ->icon('heroicon-o-qr-code')
                ->visible(fn (PaymentMethod $record): bool =>
                    $record->type === PaymentMethodType::QRIS && $record->qr_image
                )
                ->schema([
                    Infolists\Components\ImageEntry::make('qr_image')
                        ->label('')
                        ->disk('r2')
                        ->height(300)
                        ->extraImgAttributes(['class' => 'rounded-xl object-contain']),
                ]),
        ]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit'   => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
