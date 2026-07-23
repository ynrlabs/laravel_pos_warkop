<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\Order;
use App\Models\Outlet;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $title = 'Laporan Penjualan';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.admin.pages.sales-report-page';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Pembelian & Stok';
    }

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'outlet_id' => auth()->user()->role === 'super_admin' ? null : auth()->user()->outlet_id,
            'payment_method' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->schema([
                        Grid::make(4)->schema([
                            DatePicker::make('start_date')
                                ->label('Tanggal Mulai')
                                ->required()
                                ->live(),
                            DatePicker::make('end_date')
                                ->label('Tanggal Selesai')
                                ->required()
                                ->live(),
                            Select::make('outlet_id')
                                ->label('Outlet')
                                ->options(Outlet::pluck('name', 'id'))
                                ->placeholder('Semua Outlet')
                                ->default(fn() => auth()->user()->role === 'super_admin' ? null : auth()->user()->outlet_id)
                                ->disabled(fn() => auth()->user()->role !== 'super_admin')
                                ->live(),
                            Select::make('payment_method')
                                ->label('Metode Pembayaran')
                                ->options([
                                    'cash' => 'Tunai',
                                    'qris_dynamic' => 'QRIS Dinamis',
                                    'qris_static' => 'QRIS Statis',
                                    'ewallet_gopay' => 'GoPay',
                                    'ewallet_shopeepay' => 'ShopeePay',
                                    'ewallet_dana' => 'DANA',
                                    'ewallet_ovo' => 'OVO',
                                    'edc_debit' => 'EDC Debit',
                                    'edc_credit' => 'EDC Kredit',
                                ])
                                ->placeholder('Semua Metode')
                                ->live(),
                        ]),
                    ])
                    ->compact(),
            ])
            ->statePath('data');
    }

    public function getReportDataProperty()
    {
        $filters = $this->form->getState();

        $query = Order::query()
            ->with(['outlet', 'user'])
            ->where('status', 'paid');

        if (!empty($filters['start_date'])) {
            $query->whereDate('paid_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('paid_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['outlet_id'])) {
            $query->where('outlet_id', $filters['outlet_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return $query->orderBy('paid_at', 'desc')->get();
    }

    public function getStatsProperty(): array
    {
        $orders = $this->report_data;

        $grossSales = $orders->sum('total');
        $netSales = $orders->sum('subtotal');
        $tax = $orders->sum('tax_amount');
        $service = $orders->sum('service_charge');
        $count = $orders->count();

        return [
            'gross_sales' => $grossSales,
            'net_sales' => $netSales,
            'tax' => $tax,
            'service_charge' => $service,
            'count' => $count,
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $orders = $this->report_data;
        $filters = $this->form->getState();
        
        $filename = 'laporan-penjualan-' . ($filters['start_date'] ?? today()->format('Y-m-d')) . '-to-' . ($filters['end_date'] ?? today()->format('Y-m-d')) . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID Order', 'Outlet', 'Kasir', 'Tanggal', 'Subtotal', 'Pajak', 'Service Charge', 'Total', 'Metode Pembayaran'];

        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                fputcsv($file, [
                    '#' . $order->id,
                    $order->outlet?->name ?? '-',
                    $order->user?->name ?? '-',
                    $order->paid_at->format('Y-m-d H:i:s'),
                    $order->subtotal,
                    $order->tax_amount,
                    $order->service_charge,
                    $order->total,
                    $order->payment_method,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
