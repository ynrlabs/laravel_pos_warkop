<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SupplierResource\Pages;
use App\Models\Supplier;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedTruck;
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Pembelian & Stok';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Supplier')
                    ->schema([
                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->user()->outlet_id ?? null)
                            ->disabled(fn () => auth()->user()->role !== 'super_admin'),

                        TextInput::make('code')
                            ->label('Kode Supplier')
                            ->required()
                            ->maxLength(20)
                            ->unique(Supplier::class, 'code', ignoreRecord: true),

                        TextInput::make('name')
                            ->label('Nama Supplier')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->maxLength(100),

                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(100),

                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3),

                        TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(30),

                        TextInput::make('payment_terms')
                            ->label('Tempo Pembayaran (Hari)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('0 = COD'),

                        TextInput::make('bank_account')
                            ->label('No. Rekening')
                            ->maxLength(100),

                        TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->maxLength(50),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['outlet']))
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('name')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(30),

                TextColumn::make('contact_person')
                    ->label('Contact Person')
                    ->toggleable()
                    ->limit(25),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(),

                TextColumn::make('payment_terms')
                    ->label('Tempo (Hari)')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state === 0 ? 'COD' : $state . ' hari')
                    ->badge()
                    ->color(fn ($state) => $state === 0 ? 'warning' : 'info'),

                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_active')
                    ->label('Status')
                    ->sortable(),

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
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
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