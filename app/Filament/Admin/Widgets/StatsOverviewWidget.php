<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\Ingredient;
use App\Models\WasteLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. Today's Revenue & Yesterday's Revenue
        $todayRevenue = Order::where('status', 'paid')
            ->whereDate('paid_at', Carbon::today())
            ->sum('total');

        $yesterdayRevenue = Order::where('status', 'paid')
            ->whereDate('paid_at', Carbon::yesterday())
            ->sum('total');

        $revenueDiff = $todayRevenue - $yesterdayRevenue;
        $revenueIcon = $revenueDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $revenueColor = $revenueDiff >= 0 ? 'success' : 'danger';
        $revenueDesc = $revenueDiff >= 0 
            ? 'Naik Rp ' . number_format($revenueDiff, 0, ',', '.') . ' dr kemarin'
            : 'Turun Rp ' . number_format(abs($revenueDiff), 0, ',', '.') . ' dr kemarin';

        // 2. Today's Transactions & Yesterday's Transactions
        $todayOrders = Order::where('status', 'paid')
            ->whereDate('paid_at', Carbon::today())
            ->count();

        $yesterdayOrders = Order::where('status', 'paid')
            ->whereDate('paid_at', Carbon::yesterday())
            ->count();

        $ordersDiff = $todayOrders - $yesterdayOrders;
        $ordersIcon = $ordersDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $ordersColor = $ordersDiff >= 0 ? 'success' : 'danger';
        $ordersDesc = $ordersDiff >= 0
            ? 'Naik ' . $ordersDiff . ' transaksi dr kemarin'
            : 'Turun ' . abs($ordersDiff) . ' transaksi dr kemarin';

        // 3. Critical Ingredients (Current Stock <= Min Stock)
        $criticalStockCount = Ingredient::whereColumn('current_stock', '<=', 'min_stock')
            ->count();

        // 4. Today's Waste Cost
        $todayWaste = WasteLog::where('status', 'approved')
            ->whereDate('waste_date', Carbon::today())
            ->get()
            ->sum(fn($log) => $log->quantity * $log->unit_cost);

        return [
            Stat::make('Omset Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description($revenueDesc)
                ->descriptionIcon($revenueIcon)
                ->color($revenueColor),

            Stat::make('Transaksi Hari Ini', $todayOrders . ' Order')
                ->description($ordersDesc)
                ->descriptionIcon($ordersIcon)
                ->color($ordersColor),

            Stat::make('Bahan Baku Kritis', $criticalStockCount . ' Item')
                ->description($criticalStockCount > 0 ? 'Butuh restock segera' : 'Stok aman')
                ->descriptionIcon($criticalStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($criticalStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Biaya Waste Hari Ini', 'Rp ' . number_format($todayWaste, 0, ',', '.'))
                ->description('Bahan/produk terbuang (disetujui)')
                ->descriptionIcon('heroicon-m-trash')
                ->color($todayWaste > 0 ? 'warning' : 'gray'),
        ];
    }
}
