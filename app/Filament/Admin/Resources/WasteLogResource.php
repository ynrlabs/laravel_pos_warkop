<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WasteLogResource\Pages;
use App\Models\WasteLog;
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
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class WasteLogResource extends Resource
{
    protected static ?string $model = WasteLog::class;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedTrash;
    }

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Pembelian & Stok';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Waste Log')
                    ->schema([
                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->user()->outlet_id ?? null)
                            ->disabled(fn () => auth()->user()->role !== 'super_admin'),

                        TextInput::make('waste_number')
                            ->label('Nomor Waste')
                            ->required()
                            ->maxLength(30)
                            ->unique(WasteLog::class, 'waste_number', ignoreRecord: true),

                        DatePicker::make('waste_date')
                            ->label('Tanggal Waste')
                            ->default(now())
                            ->required(),

                        TimePicker::make('waste_time')
                            ->label('Waktu Waste')
                            ->default(now())
                            ->seconds(false)
                            ->required(),

                        Select::make('ingredient_id')
                            ->label('Bahan Baku')
                            ->relationship('ingredient', 'name')
                            ->searchable()
                            ->preload()
                            ->requiredWithOut('product_id')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if ($state) {
                                    $ingredient = \App\Models\Ingredient::find($state);
                                    if ($ingredient) {
                                        $set('unit_id', $ingredient->unit_id);
                                        $set('unit_cost', $ingredient->avg_cost);
                                    }
                                }
                            }),

                        Select::make('product_id')
                            ->label('Produk Jadi')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->requiredWithOut('ingredient_id')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if ($state) {
                                    $product = \App\Models\Product::find($state);
                                    if ($product) {
                                        $set('unit_id', $product->unit_id);
                                    }
                                }
                            }),

                        Select::make('product_variant_id')
                            ->label('Varian Produk')
                            ->relationship('variant', 'name')
                            ->searchable()
                            ->preload(),

                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->step(0.0001)
                            ->required()
                            ->default(1),

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

                        Select::make('reason')
                            ->label('Alasan Waste')
                            ->options([
                                'expired' => 'Kadaluarsa',
                                'spoiled' => 'Busuk/Rusak',
                                'burnt' => 'Hangus',
                                'dropped' => 'Terjatuh',
                                'overcooked' => 'Terlalu Matang',
                                'wrong_order' => 'Salah Order',
                                'sample' => 'Sampel/Uji Coba',
                                'other' => 'Lainnya',
                            ])
                            ->required(),

                        Textarea::make('reason_detail')
                            ->label('Detail Alasan')
                            ->rows(2)
                            ->placeholder('Jelaskan detail kejadian waste...'),

                        FileUpload::make('photo_path')
                            ->label('Foto Bukti Waste')
                            ->image()
                            ->directory('waste-logs')
                            ->maxSize(2048)
                            ->columnSpanFull()
                            ->helperText('Wajib untuk waste nilai tinggi'),

                        Select::make('reported_by')
                            ->label('Dilaporkan Oleh')
                            ->relationship('reportedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(auth()->id())
                            ->disabled(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('pending')
                            ->required(),

                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approvedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('status') === 'approved'),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['outlet', 'ingredient', 'product', 'reportedBy', 'approvedBy']))
            ->columns([
                TextColumn::make('waste_number')
                    ->label('Nomor Waste')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('ingredient.name')
                    ->label('Bahan Baku')
                    ->searchable()
                    ->limit(30)
                    ->placeholder(fn (WasteLog $record) => $record->product?->name ?? '-'),

                TextColumn::make('product.name')
                    ->label('Produk Jadi')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('-'),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric(decimalPlaces: 4)
                    ->suffix(fn (WasteLog $record) => ' ' . ($record->unit?->code ?? '')),

                TextColumn::make('total_cost')
                    ->label('Total Nilai')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->badge()
                    ->colors([
                        'warning' => 'expired',
                        'danger' => 'spoiled',
                        'gray' => 'burnt',
                        'info' => 'dropped',
                        'primary' => 'overcooked',
                        'success' => 'wrong_order',
                        'purple' => 'sample',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'expired' => 'Kadaluarsa',
                        'spoiled' => 'Busuk/Rusak',
                        'burnt' => 'Hangus',
                        'dropped' => 'Terjatuh',
                        'overcooked' => 'Terlalu Matang',
                        'wrong_order' => 'Salah Order',
                        'sample' => 'Sampel',
                        'other' => 'Lainnya',
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('waste_date')
                    ->label('Tgl Waste')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('reportedBy.name')
                    ->label('Dilaporkan Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->size(40)
                    ->circular()
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

                SelectFilter::make('reason')
                    ->label('Alasan')
                    ->options([
                        'expired' => 'Kadaluarsa',
                        'spoiled' => 'Busuk/Rusak',
                        'burnt' => 'Hangus',
                        'dropped' => 'Terjatuh',
                        'overcooked' => 'Terlalu Matang',
                        'wrong_order' => 'Salah Order',
                        'sample' => 'Sampel',
                        'other' => 'Lainnya',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (WasteLog $record) => $record->status === 'pending')
                        ->action(fn (WasteLog $record) => $record->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()])),
                    Tables\Actions\Action::make('reject')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (WasteLog $record) => $record->status === 'pending')
                        ->action(fn (WasteLog $record) => $record->update(['status' => 'rejected'])),
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
            'index' => Pages\ListWasteLogs::route('/'),
            'create' => Pages\CreateWasteLog::route('/create'),
            'edit' => Pages\EditWasteLog::route('/{record}/edit'),
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