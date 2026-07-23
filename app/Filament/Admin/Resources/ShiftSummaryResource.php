<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ShiftSummaryResource\Pages;
use App\Models\ShiftSummary;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteBulkAction;

class ShiftSummaryResource extends Resource
{
    protected static ?string $model = ShiftSummary::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return Heroicon::OutlinedDocumentChartBar;
    }

    protected static ?string $navigationLabel = 'Shift Summaries';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Shift Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->shiftDefinition->name} - {$record->shift_date} ({$record->user->name})")
                    ->required(),

                TextInput::make('opening_cash')
                    ->label('Modal Awal')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),

                TextInput::make('cash_sales')
                    ->label('Penjualan Tunai')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),

                TextInput::make('non_cash_sales')
                    ->label('Penjualan Non-Tunai')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),

                TextInput::make('refund_amount')
                    ->label('Refund')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),

                TextInput::make('expected_cash')
                    ->label('Ekspektasi Kas')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),

                TextInput::make('actual_cash')
                    ->label('Kas Fisik')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),

                TextInput::make('variance')
                    ->label('Selisih')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),

                Textarea::make('variance_notes')
                    ->label('Catatan Selisih')
                    ->rows(3)
                    ->disabled(),

                TextInput::make('transaction_count')
                    ->label('Jumlah Transaksi')
                    ->numeric()
                    ->disabled(),

                TextInput::make('void_count')
                    ->label('Void')
                    ->numeric()
                    ->disabled(),

                TextInput::make('refund_count')
                    ->label('Refund')
                    ->numeric()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shift.shiftDefinition.name')
                    ->label('Shift')
                    ->sortable(),

                TextColumn::make('shift.shift_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('shift.user.name')
                    ->label('Kasir')
                    ->sortable(),

                TextColumn::make('opening_cash')
                    ->label('Modal Awal')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('cash_sales')
                    ->label('Penjualan Tunai')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('non_cash_sales')
                    ->label('Penjualan Non-Tunai')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('expected_cash')
                    ->label('Ekspektasi')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('actual_cash')
                    ->label('Kas Fisik')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('variance')
                    ->label('Selisih')
                    ->money('IDR')
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->sortable(),

                BadgeColumn::make('shift.status')
                    ->label('Status Shift')
                    ->colors([
                        'warning' => 'opened',
                        'gray' => 'closed',
                        'success' => 'reconciled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'opened' => 'Dibuka',
                        'closed' => 'Ditutup',
                        'reconciled' => 'Direkonsiliasi',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                SelectFilter::make('shift.outlet_id')
                    ->label('Outlet')
                    ->relationship('shift.outlet', 'name'),

                Filter::make('shift_date')
                    ->form([
                        Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data): void {
                        if ($data['from']) {
                            $query->whereHas('shift', fn ($q) => $q->whereDate('shift_date', '>=', $data['from']));
                        }
                        if ($data['until']) {
                            $query->whereHas('shift', fn ($q) => $q->whereDate('shift_date', '<=', $data['until']));
                        }
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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
            'index' => Pages\ListShiftSummaries::route('/'),
            'view' => Pages\ViewShiftSummary::route('/{record}'),
        ];
    }
}