<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Katalog';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $modelLabel      = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';
    protected static ?int    $navigationSort  = 1;

    // ── Form ──────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ── Kolom Kiri: Info Produk ──────────────────────────────────────
            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(150)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                // Auto-generate slug hanya jika slug masih kosong / belum diubah manual
                                if (! $get('slug') || $get('slug') === Str::slug($get('name'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->maxLength(150)
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->helperText('Otomatis dari nama produk (Hanya Baca).')
                            ->prefix('p/')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Kategori')
                                    ->required()
                                    ->live(debounce: 400)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi Produk')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'bulletList', 'orderedList', 'blockquote',
                                'h2', 'h3', 'link',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])->columnSpan(2),

            // ── Kolom Kanan: Gambar & Setting ────────────────────────────────
            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Thumbnail Produk')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('thumbnail')
                            ->label('')
                            ->image()
                            ->disk('products')
                            ->directory('thumbnails')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('800')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Maks. 2MB. Format: JPG, PNG, WebP. Akan di-crop 1:1.'),
                    ]),

                Forms\Components\Section::make('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Produk Aktif')
                            ->helperText('Produk non-aktif tidak tampil di katalog customer.')
                            ->default(true)
                            ->onColor('success'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->integer()
                            ->default(fn () => (\App\Models\Product::max('sort_order') ?? -1) + 1)
                            ->minValue(0)
                            ->helperText('Otomatis ditentukan (Hanya Baca). Gunakan drag & drop di tabel untuk mengubah urutan.')
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ])->columnSpan(1),

            // ── Harga & Spesifikasi (Lebar Penuh) ────────────────────────────
            Forms\Components\Section::make('Harga & Spesifikasi')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\TextInput::make('base_price')
                        ->label('Harga Dasar')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->step(500)
                        ->helperText('Harga sebelum modifier opsi diterapkan.'),

                    Forms\Components\TextInput::make('unit')
                        ->label('Satuan')
                        ->required()
                        ->maxLength(30)
                        ->placeholder('lembar, pcs, meter, set')
                        ->helperText('Contoh: lembar, pcs, meter'),

                    Forms\Components\TextInput::make('min_qty')
                        ->label('Minimum Quantity')
                        ->required()
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->default(1)
                        ->suffix(fn (Forms\Get $get): string => $get('unit') ?: 'pcs'),

                    Forms\Components\TextInput::make('weight_per_unit')
                        ->label('Berat per Satuan (gram)')
                        ->numeric()
                        ->nullable()
                        ->minValue(0)
                        ->step(1)
                        ->suffix('gram')
                        ->placeholder('Opsional'),
                ])
                ->columns(2)
                ->columnSpanFull(),

            // ── Opsi Produk (Repeater - Lebar Penuh) ─────────────────────────
            Forms\Components\Section::make('Opsi & Spesifikasi Produk')
                ->icon('heroicon-o-adjustments-horizontal')
                ->description('Tambahkan variasi seperti Ukuran, Bahan, Laminasi, dll.')
                ->schema([
                    Forms\Components\Repeater::make('options')
                        ->label('')
                        ->relationship('options')
                        ->schema([
                            Forms\Components\TextInput::make('group_name')
                                ->label('Grup')
                                ->required()
                                ->maxLength(50)
                                ->placeholder('Ukuran, Bahan, Laminasi...')
                                ->datalist([
                                    'Ukuran', 'Bahan / Kertas', 'Laminasi',
                                    'Finishing', 'Warna Cetak', 'Ketebalan',
                                ])
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('option_name')
                                ->label('Nama Opsi')
                                ->required()
                                ->maxLength(100)
                                ->placeholder('A4, Art Paper 260gsm, Glossy...')
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('price_modifier')
                                ->label('Modifier Harga')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->step(100)
                                ->prefix(fn (Forms\Get $get): string =>
                                    $get('modifier_type') === 'percentage' ? '%' : 'Rp'
                                )
                                ->helperText('Negatif untuk diskon.'),

                            Forms\Components\Select::make('modifier_type')
                                ->label('Tipe Modifier')
                                ->options([
                                    'fixed'      => 'Nominal Tetap (Rp)',
                                    'percentage' => 'Persentase (%)',
                                ])
                                ->default('fixed')
                                ->required()
                                ->native(false),

                            Forms\Components\TextInput::make('sort_order')
                                ->label('Urutan')
                                ->numeric()
                                ->integer()
                                ->default(0)
                                ->minValue(0),

                            Forms\Components\Toggle::make('is_default')
                                ->label('Default')
                                ->helperText('Dipilih otomatis saat order.')
                                ->inline(false),
                        ])
                        ->columns(8)
                        ->columnSpanFull()
                        ->addActionLabel('+ Tambah Opsi')
                        ->reorderable('sort_order')
                        ->collapsible()
                        ->cloneable()
                        ->itemLabel(fn (array $state): string =>
                            ($state['group_name'] ?? '—') . ': ' . ($state['option_name'] ?? '—')
                        ),
                ])
                ->columnSpanFull(),
        ])->columns(3);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('')
                    ->disk('products')
                    ->circular(false)
                    ->square()
                    ->size(52)
                    ->defaultImageUrl(fn (Product $r): string => 'https://placehold.co/52x52/f97316/fff?text=' . urlencode(substr($r->name, 0, 1))),

                Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (Product $r): string => 'p/' . $r->slug),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('formatted_base_price')
                    ->label('Harga Dasar')
                    ->color('primary')
                    ->sortable(query: fn (Builder $q, string $dir) => $q->orderBy('base_price', $dir)),

                Tables\Columns\TextColumn::make('options_count')
                    ->label('Opsi')
                    ->counts('options')
                    ->suffix(' opsi')
                    ->alignCenter()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('sort_order')
            ->reorderable('sort_order')

            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non-aktif')
                    ->placeholder('Semua'),

                Tables\Filters\TrashedFilter::make(),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Product $r): string => $r->is_active ? 'Non-aktifkan' : 'Aktifkan')
                    ->icon(fn (Product $r): string => $r->is_active ? 'heroicon-m-eye-slash' : 'heroicon-m-eye')
                    ->color(fn (Product $r): string => $r->is_active ? 'warning' : 'success')
                    ->action(fn (Product $record) => $record->update(['is_active' => ! $record->is_active]))
                    ->requiresConfirmation(fn (Product $r): bool => $r->is_active) // Konfirmasi hanya saat menonaktifkan
                    ->modalDescription(fn (Product $r): string => "Non-aktifkan produk \"{$r->name}\"? Produk tidak akan tampil di katalog."),

                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
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
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])

            ->defaultSort('sort_order')
            ->striped();
    }

    // ── Eloquent Query (include soft-deleted + eager load) ────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->with(['category', 'options']);
    }

    // ── Navigation Badge (produk non-aktif) ───────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $inactive = Product::where('is_active', false)->count();
        return $inactive > 0 ? (string) $inactive : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
