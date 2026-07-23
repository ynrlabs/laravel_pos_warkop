<x-filament-panels::page>
    <div class="space-y-6 print:space-y-4 print:p-0">
        <!-- Filter Form (Hidden on Print) -->
        <div class="print:hidden">
            {{ $this->form }}
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 print:grid-cols-5 print:gap-2">
            <!-- Stats Cards -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Transaksi</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $this->stats['count'] }}</span>
                <span class="text-xs text-gray-400 mt-1">Transaksi Lunas</span>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pendapatan Kotor</span>
                <span class="text-2xl font-bold text-amber-600 mt-2">Rp {{ number_format($this->stats['gross_sales'], 0, ',', '.') }}</span>
                <span class="text-xs text-gray-400 mt-1">Gross Revenue</span>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pendapatan Bersih</span>
                <span class="text-2xl font-bold text-emerald-600 mt-2">Rp {{ number_format($this->stats['net_sales'], 0, ',', '.') }}</span>
                <span class="text-xs text-gray-400 mt-1">Subtotal Saja</span>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pajak</span>
                <span class="text-2xl font-bold text-blue-600 mt-2">Rp {{ number_format($this->stats['tax'], 0, ',', '.') }}</span>
                <span class="text-xs text-gray-400 mt-1">Pajak PPN</span>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Service Charge</span>
                <span class="text-2xl font-bold text-purple-600 mt-2">Rp {{ number_format($this->stats['service_charge'], 0, ',', '.') }}</span>
                <span class="text-xs text-gray-400 mt-1">Biaya Layanan</span>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <!-- Header Table Actions (Hidden on Print) -->
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center print:hidden">
                <h3 class="font-semibold text-gray-900 dark:text-white">Detail Transaksi Penjualan</h3>
                <div class="flex gap-2">
                    <button
                        wire:click="exportCsv"
                        class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1.5"
                    >
                        <i class="heroicon-o-arrow-down-tray w-4 h-4"></i> Ekspor CSV
                    </button>
                    <button
                        onclick="window.print()"
                        class="px-3 py-1.5 text-xs font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors flex items-center gap-1.5"
                    >
                        <i class="heroicon-o-printer w-4 h-4"></i> Cetak Laporan
                    </button>
                </div>
            </div>

            <!-- Print-Only Header -->
            <div class="hidden print:block text-center border-b pb-4 mb-4">
                <h1 class="text-xl font-bold text-black">LAPORAN PENJUALAN WARKOP POS</h1>
                <p class="text-sm text-gray-600">
                    Periode: {{ Carbon\Carbon::parse($this->data['start_date'])->format('d M Y') }} s/d {{ Carbon\Carbon::parse($this->data['end_date'])->format('d M Y') }}
                </p>
                @if(!empty($this->data['outlet_id']))
                    <p class="text-sm text-gray-600">Outlet: {{ App\Models\Outlet::find($this->data['outlet_id'])?->name }}</p>
                @endif
            </div>

            <!-- Table content -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 print:text-black">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 print:bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 font-semibold">ID Order</th>
                            <th class="px-4 py-3 font-semibold">Outlet</th>
                            <th class="px-4 py-3 font-semibold">Kasir</th>
                            <th class="px-4 py-3 font-semibold text-center">Tanggal & Waktu</th>
                            <th class="px-4 py-3 font-semibold text-right">Subtotal</th>
                            <th class="px-4 py-3 font-semibold text-right">Pajak</th>
                            <th class="px-4 py-3 font-semibold text-right">Layanan</th>
                            <th class="px-4 py-3 font-semibold text-right">Total</th>
                            <th class="px-4 py-3 font-semibold text-center">Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 print:divide-gray-300">
                        @forelse($this->report_data as $order)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">#{{ $order->id }}</td>
                                <td class="px-4 py-3.5">{{ $order->outlet?->name ?? '-' }}</td>
                                <td class="px-4 py-3.5">{{ $order->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-center">{{ $order->paid_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3.5 text-right font-mono">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 text-right font-mono">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 text-right font-mono">Rp {{ number_format($order->service_charge, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold font-mono text-gray-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 uppercase">
                                        {{ str_replace('_', ' ', str_replace('ewallet_', '', $order->payment_method)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                    Tidak ada data transaksi lunas pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50/80 dark:bg-gray-700/80 font-semibold text-gray-900 dark:text-white print:bg-gray-100 print:text-black">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right">TOTAL</td>
                            <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($this->stats['net_sales'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($this->stats['tax'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($this->stats['service_charge'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-amber-600">Rp {{ number_format($this->stats['gross_sales'], 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Only Styles -->
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .fi-sidebar,
            .fi-topbar,
            .fi-breadcrumbs,
            .fi-header,
            .fi-footer,
            .print\:hidden {
                display: none !important;
            }
            .fi-main {
                padding: 0 !important;
                margin: 0 !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #ddd !important;
                padding: 6px !important;
            }
            thead {
                display: table-header-group !important;
            }
        }
    </style>
</x-filament-panels::page>
