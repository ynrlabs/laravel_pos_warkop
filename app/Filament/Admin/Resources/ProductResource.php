<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use BackedEnum;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedCube;
    }

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Produk')
                    ->schema([
                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->user()->outlet_id ?? null)
                            ->disabled(fn () => auth()->user()->role !== 'super_admin'),
                        
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(120)
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(500),
                        
                        FileUpload::make('image_path')
                            ->label('Gambar')
                            ->image()
                            ->directory('products')
                            ->maxSize(1024)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Harga & Tipe')
                    ->schema([
                        Select::make('type')
                            ->label('Tipe Produk')
                            ->options([
                                'finished_good' => 'Produk Jadi (Dijual)',
                                'raw_material' => 'Bahan Baku (Untuk Recipe)',
                            ])
                            ->required()
                            ->default('finished_good')
                            ->reactive(),
                        
                        TextInput::make('base_price')
                            ->label('Harga Dasar')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(100)
                            ->default(0)
                            ->required()
                            ->visible(fn (Get $get) => $get('type') === 'finished_good'),
                        
                        TextInput::make('cost_price')
                            ->label('HPP (Otomatis dari Recipe)')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(0.01)
                            ->disabled()
                            ->helperText('Dihitung otomatis dari recipe/BOM'),
                        
                        TextInput::make('prep_time_minutes')
                            ->label('Waktu Persiapan (Menit)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('menit'),
                    ])->columns(2),

                Section::make('Stok (Produk Jadi)')
                    ->schema([
                        TextInput::make('stock_qty')
                            ->label('Stok Saat Ini')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        
                        TextInput::make('min_stock')
                            ->label('Minimum Stok')
                            ->numeric()
                            ->default(5)
                            ->minValue(0),
                        
                        TextInput::make('max_stock')
                            ->label('Maksimum Stok')
                            ->numeric()
                            ->nullable()
                            ->minValue(0),
                        
                        Toggle::make('track_stock')
                            ->label('Track Stok')
                            ->default(true),
                    ])->columns(4)
                    ->visible(fn (Get $get) => $get('type') === 'finished_good'),

                Section::make('Tampilan POS')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif di POS')
                            ->default(true),
                        
                        Toggle::make('is_bestseller')
                            ->label('Best Seller')
                            ->default(false),
                        
                        Toggle::make('is_combo')
                            ->label('Produk Kombo')
                            ->default(false)
                            ->helperText('Paket bundling (contoh: Nasi + Ayam + Es Teh)'),
                        
                        TextInput::make('sort_order')
                            ->label('Urutan di POS')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        
                        Select::make('kds_station')
                            ->label('Station KDS')
                            ->options([
                                'barista' => 'Barista (Minuman)',
                                'kitchen' => 'Kitchen (Makanan)',
                                'both' => 'Keduanya',
                            ])
                            ->placeholder('Pilih station...'),
                    ])->columns(2),

                Section::make('Metadata Tambahan')
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadata (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Tambah'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'outlet']))
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Gambar')
                    ->size(40)
                    ->circular(),
                
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(30),
                
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'finished_good',
                        'warning' => 'raw_material',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'finished_good' ? 'Produk Jadi' : 'Bahan Baku'),
                
                TextColumn::make('base_price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                
                TextColumn::make('cost_price')
                    ->label('HPP')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                
                IconColumn::make('is_bestseller')
                    ->label('Best Seller')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-circle')
                    ->toggleable(),
                
                TextColumn::make('stock_qty')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->stock_qty <= $record->min_stock ? 'danger' : 'success')
                    ->toggleable(),
                
                TextColumn::make('variants_count')
                    ->label('Varian')
                    ->counts('variants')
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->default(fn () => auth()->user()->outlet_id ?? null),
                
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'finished_good' => 'Produk Jadi',
                        'raw_material' => 'Bahan Baku',
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->native(false),
                
                TernaryFilter::make('is_bestseller')
                    ->label('Best Seller')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->native(false),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                    Actions\Action::make('manage_variants')
                        ->label('Kelola Varian')
                        ->icon('heroicon-o-cube-transparent')
                        ->url(fn (Product $record) => route('filament.admin.resources.product-variants.index', ['product' => $record])),
                    Actions\Action::make('manage_modifiers')
                        ->label('Kelola Modifier')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->url(fn (Product $record) => route('filament.admin.resources.modifiers.index', ['product' => $record])),
                    Actions\Action::make('manage_toppings')
                        ->label('Kelola Topping')
                        ->icon('heroicon-o-plus-circle')
                        ->url(fn (Product $record) => route('filament.admin.resources.toppings.index', ['product' => $record])),
                    Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (auth()->user()->role !== 'super_admin') {
            $query->where('outlet_id', auth()->user()->outlet_id);
        }
        
        return $query;
    }
}