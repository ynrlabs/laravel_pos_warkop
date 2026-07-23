<?php

namespace App\Filament\Admin\Resources\ShiftResource\Pages;

use App\Filament\Admin\Resources\ShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShift extends EditRecord
{
    protected static string $resource = ShiftResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('close_shift')
                ->label('Tutup Shift')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->visible(fn ($record) => $record->status === 'opened')
                ->requiresConfirmation()
                ->modalHeading('Tutup Shift')
                ->modalDescription('Apakah Anda yakin ingin menutup shift ini? Pastikan semua transaksi sudah selesai.')
                ->modalSubmitActionLabel('Ya, Tutup Shift')
                ->action(function ($record) {
                    $record->update([
                        'status' => 'closed',
                        'closed_at' => now(),
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Shift ditutup')
                        ->body('Shift berhasil ditutup. Silakan lakukan rekonsiliasi kas.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reconcile')
                ->label('Rekonsiliasi')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->visible(fn ($record) => $record->status === 'closed')
                ->form([
                    \Filament\Schemas\Components\Section::make('Input Kas Fisik di Laci')
                        ->schema([
                            \Filament\Forms\Components\Repeater::make('closing_cash')
                                ->label('Denominasi Uang')
                                ->schema([
                                    \Filament\Forms\Components\Select::make('denomination')
                                        ->label('Nominal')
                                        ->options([
                                            100000 => 'Rp 100.000',
                                            50000 => 'Rp 50.000',
                                            20000 => 'Rp 20.000',
                                            10000 => 'Rp 10.000',
                                            5000 => 'Rp 5.000',
                                            2000 => 'Rp 2.000',
                                            1000 => 'Rp 1.000',
                                            500 => 'Rp 500',
                                            200 => 'Rp 200',
                                            100 => 'Rp 100',
                                        ])
                                        ->required()
                                        ->live(),
                                    \Filament\Forms\Components\TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(0)
                                        ->required()
                                        ->live(),
                                    \Filament\Forms\Components\TextInput::make('subtotal')
                                        ->label('Subtotal')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->disabled()
                                        ->dehydrated(false),
                                ])
                                ->columns(3)
                                ->defaultItems([
                                    ['denomination' => 100000, 'quantity' => 0],
                                    ['denomination' => 50000, 'quantity' => 0],
                                    ['denomination' => 20000, 'quantity' => 0],
                                    ['denomination' => 10000, 'quantity' => 0],
                                    ['denomination' => 5000, 'quantity' => 0],
                                    ['denomination' => 2000, 'quantity' => 0],
                                    ['denomination' => 1000, 'quantity' => 0],
                                    ['denomination' => 500, 'quantity' => 0],
                                    ['denomination' => 200, 'quantity' => 0],
                                    ['denomination' => 100, 'quantity' => 0],
                                ])
                                ->reactive()
                                ->afterStateUpdated(function ($state, \Filament\Set $set) {
                                    $total = 0;
                                    foreach ($state as $item) {
                                        $total += ($item['denomination'] ?? 0) * ($item['quantity'] ?? 0);
                                    }
                                    // This would need JavaScript to update subtotal per row
                                }),
                            \Filament\Forms\Components\Textarea::make('variance_notes')
                                ->label('Catatan Selisih')
                                ->placeholder('Jika ada selisih, tuliskan alasan di sini...')
                                ->rows(3),
                        ]),
                ])
                ->action(function ($record, array $data) {
                    // Save closing cash details
                    $actualCash = 0;
                    foreach ($data['closing_cash'] as $cash) {
                        $subtotal = ($cash['denomination'] ?? 0) * ($cash['quantity'] ?? 0);
                        $actualCash += $subtotal;
                        
                        $record->cashDetails()->create([
                            'type' => 'closing',
                            'denomination' => $cash['denomination'],
                            'quantity' => $cash['quantity'],
                        ]);
                    }

                    // Calculate expected cash
                    $openingCash = $record->opening_cash;
                    $cashSales = $record->total_cash_sales;
                    $refundAmount = $record->refund_amount;
                    $expectedCash = $openingCash + $cashSales - $refundAmount;
                    $variance = $actualCash - $expectedCash;

                    // Create or update summary
                    $record->summary()->updateOrCreate([], [
                        'opening_cash' => $openingCash,
                        'cash_sales' => $cashSales,
                        'non_cash_sales' => $record->total_non_cash_sales,
                        'refund_amount' => $refundAmount,
                        'expected_cash' => $expectedCash,
                        'actual_cash' => $actualCash,
                        'variance_notes' => $data['variance_notes'] ?? null,
                        'transaction_count' => $record->transaction_count,
                        'void_count' => $record->void_count,
                        'refund_count' => $record->refund_count,
                    ]);

                    // Update shift status
                    $record->update([
                        'status' => 'reconciled',
                        'reconciled_at' => now(),
                        'reconciled_by' => auth()->id(),
                    ]);

                    $message = 'Rekonsiliasi selesai. ';
                    if ($variance > 0) {
                        $message .= "Kas lebih Rp " . number_format($variance, 0, ',', '.');
                    } elseif ($variance < 0) {
                        $message .= "Kas kurang Rp " . number_format(abs($variance), 0, ',', '.');
                    } else {
                        $message .= 'Kas sesuai (selesai).';
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Rekonsiliasi Berhasil')
                        ->body($message)
                        ->success()
                        ->send();
                }),

            Actions\Action::make('view_summary')
                ->label('Lihat Ringkasan')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->visible(fn ($record) => $record->status === 'reconciled')
                ->url(fn ($record) => route('admin.shift-summary', $record)),
        ];
    }
}