<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon  = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Katalog';
    protected static ?string $navigationLabel = 'Kategori';
    protected static ?string $modelLabel      = 'Kategori';
    protected static ?string $pluralModelLabel = 'Kategori';
    protected static ?int    $navigationSort  = 2;

    // ── Form ──────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Informasi Kategori')
                    ->icon('heroicon-o-folder-open')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(100)
                            ->live(debounce: 400)
                            ->afterStateUpdated(fn (Set $set, ?string $state) =>
                                $set('slug', Str::slug($state ?? ''))
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->maxLength(100)
                            ->unique(Category::class, 'slug', ignoreRecord: true)
                            ->helperText('Otomatis dari nama (Hanya Baca).')
                            ->prefix('k/')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(500)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

            ])->columnSpan(2),

            // ── Kanan: Icon + Pengaturan ─────────────────────────────────────
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Icon Kategori')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('icon')
                            ->label('')
                            ->image()
                            ->disk('r2')
                            ->directory('category-icons')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('200')
                            ->imageResizeTargetHeight('200')
                            ->maxSize(512)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                            ->helperText('PNG/SVG/WebP, maks 512KB. Icon akan di-crop 1:1.'),
                    ]),

                Forms\Components\Section::make('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Kategori Aktif')
                            ->helperText('Kategori non-aktif tidak tampil di katalog.')
                            ->default(true)
                            ->onColor('success'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->integer()
                            ->default(fn () => (\App\Models\Category::max('sort_order') ?? -1) + 1)
                            ->minValue(0)
                            ->helperText('Otomatis ditentukan (Hanya Baca). Gunakan drag & drop di tabel untuk mengubah urutan.')
                            ->disabled()
                            ->dehydrated(),
                    ]),

            ])->columnSpan(1),

        ])->columns(3);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->label('')
                    ->disk('r2')
                    ->square()
                    ->size(44)
                    ->defaultImageUrl(fn (Category $r): string =>
                        'https://placehold.co/44x44/f97316/fff?text=' . urlencode(strtoupper(substr($r->name, 0, 2)))
                    ),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (Category $r): string => 'k/' . $r->slug),

                Tables\Columns\TextColumn::make('active_products_count')
                    ->label('Produk Aktif')
                    ->alignCenter()
                    ->suffix(' produk')
                    ->color('gray'),

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

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('sort_order')
            ->reorderable('sort_order')

            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non-aktif')
                    ->placeholder('Semua'),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Category $r): string => $r->is_active ? 'Non-aktifkan' : 'Aktifkan')
                    ->icon(fn (Category $r): string => $r->is_active ? 'heroicon-m-eye-slash' : 'heroicon-m-eye')
                    ->color(fn (Category $r): string => $r->is_active ? 'warning' : 'success')
                    ->action(fn (Category $record) => $record->update(['is_active' => ! $record->is_active]))
                    ->requiresConfirmation(fn (Category $r): bool => $r->is_active)
                    ->modalDescription(fn (Category $r): string =>
                        "Non-aktifkan kategori \"{$r->name}\"? Semua produk di dalamnya juga tidak akan tampil."
                    ),

                Tables\Actions\DeleteAction::make()
                    ->before(function (Category $record, Tables\Actions\DeleteAction $action): void {
                        // Cegah hapus jika masih ada produk di kategori ini
                        if ($record->products()->exists()) {
                            $action->halt();
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak dapat dihapus')
                                ->body("Kategori \"{$record->name}\" masih memiliki produk. Pindahkan atau hapus produknya terlebih dahulu.")
                                ->danger()
                                ->send();
                        }
                    }),
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

    // ── Eager Load ────────────────────────────────────────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
