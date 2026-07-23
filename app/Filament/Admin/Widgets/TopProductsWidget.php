<?php

namespace App\Filament\Admin\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = '5 Produk Terlaris';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'medium';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', 'paid')
                    ->select(
                        'order_items.product_name',
                        'order_items.variant_name',
                        DB::raw('SUM(order_items.quantity) as total_qty'),
                        DB::raw('SUM(order_items.subtotal) as total_amount'),
                        DB::raw('MIN(order_items.id) as id') // Filament table requires unique key
                    )
                    ->groupBy('order_items.product_name', 'order_items.variant_name')
                    ->orderBy('total_qty', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->description(fn ($record) => $record->variant_name ? '(' . $record->variant_name . ')' : null),
                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Terjual')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Omset')
                    ->money('IDR', locale: 'id'),
            ])
            ->paginated(false);
    }
}
