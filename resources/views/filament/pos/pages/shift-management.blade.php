{{-- resources/views/filament/pos/pages/shift-management.blade.php --}}

<div class="space-y-6">
    {{-- Hero Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Shift</h1>
                <p class="text-gray-500 mt-1">Buka shift, tutup shift, dan rekonsiliasi kas laci</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Kasir: {{ Auth::user()->name }}</div>
                <div class="text-sm text-gray-500">Outlet: {{ Auth::user()->outlet->name ?? '-' }}</div>
                <div class="text-sm font-medium text-primary-600 mt-1">{{ now()->format('l, d F Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Form Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form wire:submit.prevent>
            @livewire('filament-forms', ['form' => $form], key($form->getId()))
        </form>
    </div>

    {{-- Quick Stats when shift is open --}}
    @if($currentShift && $currentShift->status === 'opened')
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="text-sm text-gray-500">Modal Awal</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                    Rp {{ number_format($currentShift->opening_cash, 0, ',', '.') }}
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="text-sm text-gray-500">Penjualan Tunai</div>
                <div class="text-2xl font-bold text-green-600 mt-1">
                    Rp {{ number_format($currentShift->total_cash_sales, 0, ',', '.') }}
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="text-sm text-gray-500">Penjualan Non-Tunai</div>
                <div class="text-2xl font-bold text-blue-600 mt-1">
                    Rp {{ number_format($currentShift->total_non_cash_sales, 0, ',', '.') }}
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="text-sm text-gray-500">Total Transaksi</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                    {{ $currentShift->transaction_count }}
                </div>
            </div>
        </div>
    @endif

    {{-- Shift History (Quick View) --}}
    @if($currentShift)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Shift Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modal Awal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penjualan Tunai</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penjualan Non-Tunai</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $recentShifts = \App\Models\Shift::where('outlet_id', Auth::user()->outlet_id)
                                ->where('user_id', Auth::id())
                                ->latest('shift_date')
                                ->limit(5)
                                ->get();
                        @endphp
                        @foreach($recentShifts as $shift)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $shift->shift_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $shift->shiftDefinition->name }} ({{ $shift->shiftDefinition->code }})</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($shift->status === 'opened') bg-yellow-100 text-yellow-800
                                        @elseif($shift->status === 'closed') bg-blue-100 text-blue-800
                                        @elseif($shift->status === 'reconciled') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        @if($shift->status === 'opened') Dibuka
                                        @elseif($shift->status === 'closed') Ditutup
                                        @elseif($shift->status === 'reconciled') Direkonsiliasi
                                        @else {{ ucfirst($shift->status) }} @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-green-600 font-medium">Rp {{ number_format($shift->total_cash_sales, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-blue-600 font-medium">Rp {{ number_format($shift->total_non_cash_sales, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $shift->transaction_count }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($shift->status === 'reconciled')
                                        <a href="{{ route('pos.shift-summary', $shift) }}" class="text-primary-600 hover:text-primary-900 font-medium">Lihat Ringkasan</a>
                                    @elseif($shift->status === 'closed')
                                        <span class="text-gray-400">Menunggu rekonsiliasi</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-calculate subtotals in repeater
    document.addEventListener('livewire:load', () => {
        // This would need Alpine.js or custom JS to auto-calculate
        // For now, the subtotal is displayed but not auto-updated
    });
</script>
@endpush