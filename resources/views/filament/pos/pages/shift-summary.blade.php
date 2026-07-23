@extends('filament-panels::layout')

@section('content')
<div class="fi-page">
    <div class="fi-page-header">
        <div class="fi-page-header-tools">
            <a href="{{ route('pos.shift-management') }}" class="fi-btn fi-btn-sm fi-btn-secondary">
                <svg class="fi-btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Kembali ke Shift Management
            </a>
        </div>
        <div>
            <h1 class="fi-page-header-title">Ringkasan Shift</h1>
            <p class="fi-page-header-subtitle">
                {{ $shift->shiftDefinition->name }} ({{ $shift->shiftDefinition->code }}) - {{ $shift->shift_date->format('d F Y') }}
            </p>
        </div>
    </div>

    <div class="fi-page-content">
        @if ($shift->summary)
            <!-- Shift Info Cards -->
            <div class="fi-section">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="fi-card fi-card-bordered">
                        <div class="fi-card-header">
                            <h3 class="fi-card-title">Kasir</h3>
                        </div>
                        <div class="fi-card-content">
                            <p class="text-2xl font-bold">{{ $shift->user->name }}</p>
                        </div>
                    </div>

                    <div class="fi-card fi-card-bordered">
                        <div class="fi-card-header">
                            <h3 class="fi-card-title">Waktu Buka</h3>
                        </div>
                        <div class="fi-card-content">
                            <p class="text-2xl font-bold">{{ $shift->opened_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="fi-card fi-card-bordered">
                        <div class="fi-card-header">
                            <h3 class="fi-card-title">Waktu Rekonsiliasi</h3>
                        </div>
                        <div class="fi-card-content">
                            <p class="text-2xl font-bold">{{ $shift->reconciled_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="fi-section">
                    <h2 class="fi-section-title">Ringkasan Keuangan</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="fi-card fi-card-bordered bg-green-50">
                            <div class="fi-card-content">
                                <p class="text-sm text-gray-600">Modal Awal</p>
                                <p class="text-2xl font-bold text-green-800">Rp {{ number_format($shift->summary->opening_cash, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="fi-card fi-card-bordered bg-blue-50">
                            <div class="fi-card-content">
                                <p class="text-sm text-gray-600">Penjualan Tunai</p>
                                <p class="text-2xl font-bold text-blue-800">Rp {{ number_format($shift->summary->cash_sales, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="fi-card fi-card-bordered bg-purple-50">
                            <div class="fi-card-content">
                                <p class="text-sm text-gray-600">Penjualan Non-Tunai</p>
                                <p class="text-2xl font-bold text-purple-800">Rp {{ number_format($shift->summary->non_cash_sales, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="fi-card fi-card-bordered bg-red-50">
                            <div class="fi-card-content">
                                <p class="text-sm text-gray-600">Refund</p>
                                <p class="text-2xl font-bold text-red-800">Rp {{ number_format($shift->summary->refund_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Expected vs Actual -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="fi-card fi-card-bordered">
                            <div class="fi-card-header">
                                <h3 class="fi-card-title">Ekspektasi Kas</h3>
                            </div>
                            <div class="fi-card-content">
                                <p class="text-3xl font-bold text-gray-800">Rp {{ number_format($shift->summary->expected_cash, 0, ',', '.') }}</p>
                                <p class="text-sm text-gray-500 mt-1">Modal Awal + Penjualan Tunai - Refund</p>
                            </div>
                        </div>

                        <div class="fi-card fi-card-bordered">
                            <div class="fi-card-header">
                                <h3 class="fi-card-title">Kas Fisik</h3>
                            </div>
                            <div class="fi-card-content">
                                <p class="text-3xl font-bold text-gray-800">Rp {{ number_format($shift->summary->actual_cash, 0, ',', '.') }}</p>
                                <p class="text-sm text-gray-500 mt-1">Total uang di laci saat rekonsiliasi</p>
                            </div>
                        </div>

                        <div class="fi-card fi-card-bordered {{ $shift->summary->variance > 0 ? 'bg-green-50' : ($shift->summary->variance < 0 ? 'bg-red-50' : 'bg-gray-50') }}">
                            <div class="fi-card-header">
                                <h3 class="fi-card-title">Selisih</h3>
                            </div>
                            <div class="fi-card-content">
                                <p class="text-3xl font-bold {{ $shift->summary->variance > 0 ? 'text-green-700' : ($shift->summary->variance < 0 ? 'text-red-700' : 'text-gray-700') }}">
                                    {{ $shift->summary->variance > 0 ? '+' : '' }}Rp {{ number_format($shift->summary->variance, 0, ',', '.') }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $shift->summary->variance > 0 ? 'Kas Lebih' : ($shift->summary->variance < 0 ? 'Kas Kurang' : 'Kas Selesai') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="fi-card fi-card-bordered">
                            <div class="fi-card-content text-center">
                                <p class="text-sm text-gray-600">Total Transaksi</p>
                                <p class="text-3xl font-bold">{{ $shift->summary->transaction_count }}</p>
                            </div>
                        </div>

                        <div class="fi-card fi-card-bordered">
                            <div class="fi-card-content text-center">
                                <p class="text-sm text-gray-600">Void</p>
                                <p class="text-3xl font-bold text-yellow-600">{{ $shift->summary->void_count }}</p>
                            </div>
                        </div>

                        <div class="fi-card fi-card-bordered">
                            <div class="fi-card-content text-center">
                                <p class="text-sm text-gray-600">Refund</p>
                                <p class="text-3xl font-bold text-red-600">{{ $shift->summary->refund_count }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Variance Notes -->
                    @if ($shift->summary->variance_notes)
                    <div class="fi-card fi-card-bordered bg-yellow-50 mb-6">
                        <div class="fi-card-header">
                            <h3 class="fi-card-title">Catatan Selisih</h3>
                        </div>
                        <div class="fi-card-content">
                            <p class="text-yellow-800">{{ $shift->summary->variance_notes }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Cash Details -->
                    <div class="fi-section">
                        <h2 class="fi-section-title">Detail Denominasi</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Opening Cash Details -->
                            @if ($shift->openingCashDetails->count())
                            <div>
                                <h3 class="text-lg font-semibold mb-3">Modal Awal</h3>
                                <div class="fi-table-container">
                                    <table class="fi-table">
                                        <thead>
                                            <tr>
                                                <th>Nominal</th>
                                                <th class="text-right">Jumlah</th>
                                                <th class="text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($shift->openingCashDetails as $detail)
                                            <tr>
                                                <td>Rp {{ number_format($detail->denomination, 0, ',', '.') }}</td>
                                                <td class="text-right">{{ $detail->quantity }}</td>
                                                <td class="text-right font-medium">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                            <tr class="font-bold border-t-2">
                                                <td colspan="2" class="text-right">Total</td>
                                                <td class="text-right">Rp {{ number_format($shift->openingCashDetails->sum('subtotal'), 0, ',', '.') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            <!-- Closing Cash Details -->
                            @if ($shift->closingCashDetails->count())
                            <div>
                                <h3 class="text-lg font-semibold mb-3">Tutup Kasir (Rekonsiliasi)</h3>
                                <div class="fi-table-container">
                                    <table class="fi-table">
                                        <thead>
                                            <tr>
                                                <th>Nominal</th>
                                                <th class="text-right">Jumlah</th>
                                                <th class="text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($shift->closingCashDetails as $detail)
                                            <tr>
                                                <td>Rp {{ number_format($detail->denomination, 0, ',', '.') }}</td>
                                                <td class="text-right">{{ $detail->quantity }}</td>
                                                <td class="text-right font-medium">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                            <tr class="font-bold border-t-2">
                                                <td colspan="2" class="text-right">Total</td>
                                                <td class="text-right">Rp {{ number_format($shift->closingCashDetails->sum('subtotal'), 0, ',', '.') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Method Breakdown -->
                    @if ($shift->payments->count())
                    <div class="fi-section">
                        <h2 class="fi-section-title">Rincian Metode Pembayaran</h2>
                        <div class="fi-table-container">
                            <table class="fi-table">
                                <thead>
                                    <tr>
                                        <th>Metode</th>
                                        <th class="text-right">Jumlah Transaksi</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shift->payments->groupBy('method') as $method => $payments)
                                    <tr>
                                        <td>{{ $payments->first()->method_label }}</td>
                                        <td class="text-right">{{ $payments->count() }}</td>
                                        <td class="text-right font-medium">Rp {{ number_format($payments->where('status', 'paid')->sum('amount'), 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @else
            <div class="fi-card fi-card-bordered">
                <div class="fi-card-content text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Belum Direkonsiliasi</h3>
                    <p class="mt-1 text-gray-500">Shift ini belum melalui proses rekonsiliasi kas.</p>
                    <a href="{{ route('pos.shift-management') }}" class="mt-4 inline-block fi-btn fi-btn-primary">
                        Kembali ke Shift Management
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection