<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return Heroicon::OutlinedCalendarDays;
    }

    protected static ?string $navigationLabel = 'Shifts';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Shift Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Shift')
                    ->schema([
                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->required()
                            ->default(auth()->user()->outlet_id),

                        Select::make('shift_definition_id')
                            ->label('Shift Definition')
                            ->relationship('shiftDefinition', 'name')
                            ->required()
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->code}) {$record->start_time->format('H:i')}-{$record->end_time->format('H:i')}"),

                        Select::make('user_id')
                            ->label('Kasir')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->default(auth()->id()),

                        DatePicker::make('shift_date')
                            ->label('Tanggal Shift')
                            ->required()
                            ->default(now()),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'opened' => 'Dibuka',
                                'closed' => 'Ditutup',
                                'reconciled' => 'Direkonsiliasi',
                            ])
                            ->required()
                            ->default('opened'),

                        DateTimePicker::make('opened_at')
                            ->label('Waktu Buka')
                            ->seconds(false),

                        DateTimePicker::make('closed_at')
                            ->label('Waktu Tutup')
                            ->seconds(false),

                        DateTimePicker::make('reconciled_at')
                            ->label('Waktu Rekonsiliasi')
                            ->seconds(false),

                        Select::make('reconciled_by')
                            ->label('Direkonsiliasi Oleh')
                            ->relationship('reconciledBy', 'name')
                            ->searchable(),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])
                    ->columns(2),
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

                TextColumn::make('shiftDefinition.name')
                    ->label('Shift')
                    ->formatStateUsing(fn ($state, $record) => "{$record->shiftDefinition?->name} ({$record->shiftDefinition?->code})")
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('shift_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'opened',
                        'info' => 'closed',
                        'success' => 'reconciled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'opened' => 'Dibuka',
                        'closed' => 'Ditutup',
                        'reconciled' => 'Direkonsiliasi',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('opened_at')
                    ->label('Dibuka')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('closed_at')
                    ->label('Ditutup')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('reconciled_at')
                    ->label('Rekonsiliasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('opening_cash')
                    ->label('Modal Awal')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->opening_cash)
                    ->sortable(),

                TextColumn::make('closing_cash')
                    ->label('Tutup Kasir')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->closing_cash)
                    ->sortable(),

                TextColumn::make('total_cash_sales')
                    ->label('Penjualan Tunai')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->total_cash_sales)
                    ->sortable(),

                TextColumn::make('total_non_cash_sales')
                    ->label('Penjualan Non-Tunai')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->total_non_cash_sales)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name'),

                SelectFilter::make('shift_definition_id')
                    ->label('Shift')
                    ->relationship('shiftDefinition', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'opened' => 'Dibuka',
                        'closed' => 'Ditutup',
                        'reconciled' => 'Direkonsiliasi',
                    ]),

                Filter::make('shift_date')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data): void {
                        if ($data['from']) {
                            $query->whereDate('shift_date', '>=', $data['from']);
                        }
                        if ($data['until']) {
                            $query->whereDate('shift_date', '<=', $data['until']);
                        }
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('shift_date', 'desc');
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
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}