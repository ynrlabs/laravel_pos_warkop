<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use BackedEnum;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedRectangleStack;
    }

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Kategori')
                    ->schema([
                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->user()->outlet_id ?? null)
                            ->disabled(fn () => auth()->user()->role !== 'super_admin'),
                        
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(50)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(60)
                            ->unique(Category::class, 'slug', ignoreRecord: true),
                        
                        TextInput::make('icon')
                            ->label('Ikon (Lucide)')
                            ->maxLength(50)
                            ->helperText('Nama ikon Lucide: coffee, sandwich, cookie, cigarette, dll.'),
                        
                        ColorPicker::make('color')
                            ->label('Warna Badge')
                            ->default('#8B4513'),
                        
                        TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        
                        Toggle::make('is_kds_station')
                            ->label('Punya Station KDS')
                            ->default(false)
                            ->helperText('Apakah kategori ini punya layar KDS terpisah (Barista/Kitchen)'),
                        
                        TextInput::make('kds_station_name')
                            ->label('Nama Station KDS')
                            ->maxLength(50)
                            ->placeholder('Barista / Kitchen')
                            ->visible(fn (Forms\Get $get) => $get('is_kds_station')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('outlet'))
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                
                ColorColumn::make('color')
                    ->label('Warna')
                    ->default('#8B4513'),
                
                TextColumn::make('icon')
                    ->label('Ikon')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}" : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_kds_station')
                    ->label('KDS Station')
                    ->boolean()
                    ->toggleable(),
                
                TextColumn::make('kds_station_name')
                    ->label('Nama Station')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('products_count')
                    ->label('Produk')
                    ->counts('products')
                    ->badge()
                    ->color('primary'),
                
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
                
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->native(false),
                
                TernaryFilter::make('is_kds_station')
                    ->label('KDS Station')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->native(false),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
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