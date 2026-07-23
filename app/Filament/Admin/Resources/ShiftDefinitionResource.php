<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ShiftDefinitionResource\Pages;
use App\Models\ShiftDefinition;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

class ShiftDefinitionResource extends Resource
{
    protected static ?string $model = ShiftDefinition::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return Heroicon::OutlinedClock;
    }

    protected static ?string $navigationLabel = 'Shift Definitions';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Shift Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->required()
                    ->default(auth()->user()->outlet_id)
                    ->disabled(fn ($record) => $record !== null),

                TextInput::make('name')
                    ->label('Nama Shift')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Pagi, Siang, Malam, Full Day'),

                TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('PG, SG, ML, FD')
                    ->unique(ShiftDefinition::class, 'code', ignoreRecord: true),

                TimePicker::make('start_time')
                    ->label('Jam Buka')
                    ->required()
                    ->seconds(false)
                    ->default('06:00'),

                TimePicker::make('end_time')
                    ->label('Jam Tutup')
                    ->required()
                    ->seconds(false)
                    ->default('14:00'),

                Toggle::make('is_overnight')
                    ->label('Lewati Tengah Malam')
                    ->default(false)
                    ->helperText('Centang jika shift melewati jam 00:00'),

                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama Shift')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->badge(),

                TextColumn::make('start_time')
                    ->label('Mulai')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i'))
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('Selesai')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i'))
                    ->sortable(),

                IconColumn::make('is_overnight')
                    ->label('Overnight')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name'),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),

                TernaryFilter::make('is_overnight')
                    ->label('Overnight')
                    ->boolean()
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->native(false),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListShiftDefinitions::route('/'),
            'create' => Pages\CreateShiftDefinition::route('/create'),
            'edit' => Pages\EditShiftDefinition::route('/{record}/edit'),
        ];
    }
}