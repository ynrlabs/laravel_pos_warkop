<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StockOpnameResource\Pages;
use App\Models\StockOpname;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedClipboardDocumentList;
    }

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Pembelian & Stok';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Stock Opname')
                    ->schema([
                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->user()->outlet_id ?? null)
                            ->disabled(fn () => auth()->user()->role !== 'super_admin'),

                        TextInput::make('opname_number')
                            ->label('Nomor Opname')
                            ->required()
                            ->maxLength(30)
                            ->unique(StockOpname::class, 'opname_number', ignoreRecord: true),

                        Select::make('type')
                            ->label('Tipe Opname')
                            ->options([
                                'full' => 'Full Opname',
                                'partial' => 'Partial (Sebagian)',
                                'cycle' => 'Cycle Count',
                            ])
                            ->default('partial')
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'in_progress' => 'Sedang Proses',
                                'completed' => 'Selesai',
                                'approved' => 'Disetujui',
                            ])
                            ->default('draft')
                            ->required(),

                        DatePicker::make('opname_date')
                            ->label('Tanggal Opname')
                            ->default(now())
                            ->required(),

                        DatePicker::make('completed_at')
                            ->label('Tanggal Selesai')
                            ->visible(fn (Get $get) => in_array($get('status'), ['completed', 'approved'])),

                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approvedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('status') === 'approved'),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])->columns(2),

                Section::make('Item Opname')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('ingredient_id')
                                    ->label('Bahan Baku')
                                    ->relationship('ingredient', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if ($state) {
                                            $ingredient = \App\Models\Ingredient::find($state);
                                            if ($ingredient) {
                                                $set('unit_id', $ingredient->unit_id);
                                                $set('unit_cost', $ingredient->avg_cost);
                                            }
                                        }
                                    }),

                                TextInput::make('product_id')
                                    ->label('Produk Jadi')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('product_variant_id')
                                    ->label('Varian Produk')
                                    ->relationship('variant', 'name')
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('system_qty')
                                    ->label('Stok Sistem')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->required()
                                    ->default(0)
                                    ->disabled(),

                                TextInput::make('physical_qty')
                                    ->label('Stok Fisik')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->required()
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => 
                                        $set('variance_qty', ($get('physical_qty') ?? 0) - ($get('system_qty') ?? 0))
                                    ),

                                Select::make('unit_id')
                                    ->label('Satuan')
                                    ->relationship('unit', 'name')
                                    ->required(),

                                TextInput::make('unit_cost')
                                    ->label('Cost/Unit')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('Rp')
                                    ->required()
                                    ->default(0),

                                TextInput::make('batch_number')
                                    ->label('No. Batch')
                                    ->maxLength(50),

                                DatePicker::make('expired_at')
                                    ->label('Tanggal Expired'),

                                Textarea::make('notes')
                                    ->label('Catatan Item')
                                    ->rows(2),

                                Select::make('counted_by')
                                    ->label('Dihitung Oleh')
                                    ->relationship('countedBy', 'name')
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('variance_qty')
                                    ->label('Variansi Qty')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->disabled()
                                    ->default(0)
                                    ->helperText('Otomatis: Stok Fisik - Stok Sistem'),

                                TextInput::make('variance_value')
                                    ->label('Variansi Nilai')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->default(0)
                                    ->helperText('Otomatis: Variansi Qty × Cost/Unit'),
                            ])->columns(6)
                            ->addActionLabel('Tambah Item')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['ingredient_id'] 
                                ? \App\Models\Ingredient::find($state['ingredient_id'])?->name 
                                : ($state['product_id'] ? \App\Models\Product::find($state['product_id'])?->name : 'Item Baru')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['outlet', 'createdBy', 'approvedBy']))
            ->columns([
                TextColumn::make('opname_number')
                    ->label('Nomor Opname')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->colors([
                        'primary' => 'full',
                        'warning' => 'partial',
                        'info' => 'cycle',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'full' => 'Full Opname',
                        'partial' => 'Partial',
                        'cycle' => 'Cycle Count',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'in_progress',
                        'info' => 'completed',
                        'success' => 'approved',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'in_progress' => 'Sedang Proses',
                        'completed' => 'Selesai',
                        'approved' => 'Disetujui',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('opname_date')
                    ->label('Tgl Opname')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('total_variance_value')
                    ->label('Total Variansi')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'danger' : ($state < 0 ? 'success' : 'gray')),

                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
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

                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'full' => 'Full Opname',
                        'partial' => 'Partial',
                        'cycle' => 'Cycle Count',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'in_progress' => 'Sedang Proses',
                        'completed' => 'Selesai',
                        'approved' => 'Disetujui',
                    ]),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                    Actions\Action::make('start')
                        ->label('Mulai Opname')
                        ->icon('heroicon-o-play-circle')
                        ->color('primary')
                        ->visible(fn (StockOpname $record) => $record->status === 'draft')
                        ->action(fn (StockOpname $record) => $record->update(['status' => 'in_progress'])),
                    Actions\Action::make('complete')
                        ->label('Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (StockOpname $record) => $record->status === 'in_progress')
                        ->action(fn (StockOpname $record) => $record->update(['status' => 'completed', 'completed_at' => now()])),
                    Actions\Action::make('approve')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (StockOpname $record) => $record->status === 'completed')
                        ->action(fn (StockOpname $record) => $record->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()])),
                    Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
            'edit' => Pages\EditStockOpname::route('/{record}/edit'),
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