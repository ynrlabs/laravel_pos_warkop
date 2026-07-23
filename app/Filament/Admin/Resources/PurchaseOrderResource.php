<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use BackedEnum;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedShoppingBag;
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
                Section::make('Informasi PO')
                    ->schema([
                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->user()->outlet_id ?? null)
                            ->disabled(fn () => auth()->user()->role !== 'super_admin'),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('po_number')
                            ->label('Nomor PO')
                            ->required()
                            ->maxLength(30)
                            ->unique(PurchaseOrder::class, 'po_number', ignoreRecord: true),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Terkirim',
                                'confirmed' => 'Dikonfirmasi',
                                'partial_received' => 'Diterima Sebagian',
                                'received' => 'Diterima Semua',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('draft')
                            ->required(),

                        DatePicker::make('order_date')
                            ->label('Tanggal Order')
                            ->default(now())
                            ->required(),

                        DatePicker::make('expected_date')
                            ->label('Tanggal Estimasi Tiba'),

                        DatePicker::make('received_date')
                            ->label('Tanggal Diterima')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['received', 'partial_received'])),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])->columns(2),

                Section::make('Item PO')
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
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $state ? $set('unit_id', \App\Models\Ingredient::find($state)?->unit_id) : null),

                                TextInput::make('quantity_ordered')
                                    ->label('Qty Pesan')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->required()
                                    ->default(1),

                                Select::make('unit_id')
                                    ->label('Satuan')
                                    ->relationship('unit', 'name')
                                    ->required(),

                                TextInput::make('unit_price')
                                    ->label('Harga/Unit')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('Rp')
                                    ->required()
                                    ->default(0),

                                TextInput::make('discount_percent')
                                    ->label('Diskon %')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(0)
                                    ->maxValue(100),

                                TextInput::make('discount_amount')
                                    ->label('Diskon Nominal')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('Rp')
                                    ->default(0),

                                TextInput::make('tax_percent')
                                    ->label('Pajak %')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(0),

                                TextInput::make('tax_amount')
                                    ->label('Pajak Nominal')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('Rp')
                                    ->default(0),

                                Textarea::make('notes')
                                    ->label('Catatan Item')
                                    ->rows(2),
                            ])->columns(4)
                            ->addActionLabel('Tambah Item')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['ingredient_id'] 
                                ? \App\Models\Ingredient::find($state['ingredient_id'])?->name 
                                : 'Item Baru'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['supplier', 'outlet']))
            ->columns([
                TextColumn::make('po_number')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'sent',
                        'warning' => 'confirmed',
                        'primary' => 'partial_received',
                        'success' => 'received',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'confirmed' => 'Dikonfirmasi',
                        'partial_received' => 'Diterima Sebagian',
                        'received' => 'Diterima Semua',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('order_date')
                    ->label('Tgl Order')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('expected_date')
                    ->label('Est. Tiba')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('outlet.name')
                    ->label('Outlet')
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

                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'confirmed' => 'Dikonfirmasi',
                        'partial_received' => 'Diterima Sebagian',
                        'received' => 'Diterima Semua',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('receive')
                        ->label('Terima Barang')
                        ->icon('heroicon-o-truck')
                        ->color('success')
                        ->visible(fn (PurchaseOrder $record) => in_array($record->status, ['sent', 'confirmed', 'partial_received']))
                        ->url(fn (PurchaseOrder $record) => route('filament.admin.resources.po-receipts.create', ['po' => $record])),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
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