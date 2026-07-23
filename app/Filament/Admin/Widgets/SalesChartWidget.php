<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesChartWidget extends ChartWidget
{
    protected ?string $heading = 'Tren Omset Penjualan (30 Hari Terakhir)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Order::select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(total) as total')
            )
            ->where('status', 'paid')
            ->where('paid_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Fill missing dates with 0
        $chartData = [];
        $labels = [];
        for ($i = 29; $i >= 0; $i--) {
            $dateString = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($dateString)->translatedFormat('d M');
            $chartData[] = (float) ($data[$dateString] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Omset Penjualan (Rp)',
                    'data' => $chartData,
                    'borderColor' => '#8B4513', // Deep Coffee
                    'backgroundColor' => 'rgba(139, 69, 19, 0.1)',
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
