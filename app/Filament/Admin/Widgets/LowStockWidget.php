<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Ingredient;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?string $heading = 'Bahan Baku Menipis (Stok Kritis)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'medium';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ingredient::query()
                    ->with('unit')
                    ->whereColumn('current_stock', '<=', 'min_stock')
                    ->orderBy('current_stock', 'asc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Bahan'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn ($state, $record) => number_format($state, 2, ',', '.') . ' ' . ($record->unit?->name ?? '')),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stok Minimum')
                    ->formatStateUsing(fn ($state, $record) => number_format($state, 2, ',', '.') . ' ' . ($record->unit?->name ?? '')),
            ])
            ->paginated(false);
    }
}
