<?php

namespace App\Filament\Pos\Pages;

use App\Models\Shift;
use App\Models\ShiftDefinition;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions\Action;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ShiftManagement extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return Heroicon::OutlinedCalendarDays;
    }

    protected static ?string $navigationLabel = 'Shift Management';

    protected static ?string $title = 'Manajemen Shift';

    protected string $view = 'filament.pos.pages.shift-management';

    protected static ?int $navigationSort = 1;

    public ?Shift $currentShift = null;

    public ?ShiftDefinition $selectedShiftDefinition = null;

    public array $openingCashData = [];

    public array $closingCashData = [];

    public string $varianceNotes = '';

    public array $formData = [];

    public function mount(): void
    {
        $this->loadCurrentShift();
        $this->form->fill($this->formData);
    }

    protected function loadCurrentShift(): void
    {
        $this->currentShift = Shift::where('outlet_id', Auth::user()->outlet_id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['opened', 'closed'])
            ->latest('shift_date')
            ->first();

        if ($this->currentShift) {
            $this->formData = [
                'shift_definition_id' => $this->currentShift->shift_definition_id,
                'notes' => $this->currentShift->notes,
            ];
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Pilih Shift')
                    ->schema([
                        Select::make('shift_definition_id')
                            ->label('Shift')
                            ->options(
                                ShiftDefinition::where('outlet_id', Auth::user()->outlet_id)
                                    ->where('is_active', true)
                                    ->ordered()
                                    ->get()
                                    ->mapWithKeys(fn ($sd) => [$sd->id => "{$sd->name} ({$sd->code}) {$sd->start_time->format('H:i')}-{$sd->end_time->format('H:i')}"])
                                    ->toArray()
                            )
                            ->required()
                            ->searchable()
                            ->placeholder('Pilih shift')
                            ->disabled(fn () => $this->currentShift !== null)
                            ->reactive()
                            ->afterStateUpdated(fn ($state) => $this->selectedShiftDefinition = ShiftDefinition::find($state)),

                        DatePicker::make('shift_date')
                            ->label('Tanggal Shift')
                            ->default(now())
                            ->disabled(fn () => $this->currentShift !== null),
                    ])
                    ->columns(2)
                    ->visible(fn () => $this->currentShift === null),

                Section::make('Modal Awal (Buka Shift)')
                    ->schema([
                        Repeater::make('opening_cash')
                            ->label('Denominasi Uang')
                            ->schema([
                                Select::make('denomination')
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
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->live(),
                                TextInput::make('subtotal')
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
                            ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, $get) {
                                // Calculate total opening cash
                                $total = 0;
                                foreach ($state as $item) {
                                    $total += ($item['denomination'] ?? 0) * ($item['quantity'] ?? 0);
                                }
                                // Store in a hidden field or session
                                session(['opening_cash_total' => $total]);
                            }),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->placeholder('Catatan tambahan untuk shift ini...'),
                    ])
                    ->visible(fn () => $this->currentShift === null),

                Section::make('Status Shift Saat Ini')
                    ->schema([
                        TextInput::make('current_shift_name')
                            ->label('Shift')
                            ->disabled()
                            ->default(fn () => $this->currentShift?->shiftDefinition?->name ?? '-'),

                        TextInput::make('current_shift_status')
                            ->label('Status')
                            ->disabled()
                            ->default(fn () => match($this->currentShift?->status) {
                                'opened' => 'Dibuka',
                                'closed' => 'Ditutup',
                                'reconciled' => 'Direkonsiliasi',
                                default => '-',
                            }),

                        TextInput::make('current_shift_date')
                            ->label('Tanggal')
                            ->disabled()
                            ->default(fn () => $this->currentShift?->shift_date?->format('d M Y') ?? '-'),

                        TextInput::make('current_shift_opened_at')
                            ->label('Dibuka Pada')
                            ->disabled()
                            ->default(fn () => $this->currentShift?->opened_at?->format('d M Y H:i') ?? '-'),

                        TextInput::make('current_shift_opening_cash')
                            ->label('Modal Awal')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift?->opening_cash ? number_format($this->currentShift->opening_cash, 0, ',', '.') : '-'),

                        TextInput::make('current_shift_cash_sales')
                            ->label('Penjualan Tunai')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift?->total_cash_sales ? number_format($this->currentShift->total_cash_sales, 0, ',', '.') : '-'),

                        TextInput::make('current_shift_non_cash_sales')
                            ->label('Penjualan Non-Tunai')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift?->total_non_cash_sales ? number_format($this->currentShift->total_non_cash_sales, 0, ',', '.') : '-'),

                        TextInput::make('current_shift_expected_cash')
                            ->label('Ekspektasi Kas')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift && $this->currentShift->summary ? number_format($this->currentShift->summary->expected_cash, 0, ',', '.') : '-'),

                        TextInput::make('current_shift_transaction_count')
                            ->label('Jumlah Transaksi')
                            ->disabled()
                            ->default(fn () => $this->currentShift?->transaction_count ?? '-'),
                    ])
                    ->columns(3)
                    ->visible(fn () => $this->currentShift !== null),

                Section::make('Tutup Shift & Rekonsiliasi')
                    ->schema([
                        Repeater::make('closing_cash')
                            ->label('Input Kas Fisik di Laci')
                            ->schema([
                                Select::make('denomination')
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
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->live(),
                                TextInput::make('subtotal')
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
                            ->reactive(),

                        Textarea::make('variance_notes')
                            ->label('Catatan Selisih (jika ada)')
                            ->rows(2)
                            ->placeholder('Jika kas lebih/kurang, tuliskan alasan di sini...'),
                    ])
                    ->visible(fn () => $this->currentShift !== null && $this->currentShift->status === 'closed'),

                Section::make('Ringkasan Rekonsiliasi')
                    ->schema([
                        TextInput::make('summary_opening_cash')
                            ->label('Modal Awal')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift?->summary ? number_format($this->currentShift->summary->opening_cash, 0, ',', '.') : '-'),

                        TextInput::make('summary_cash_sales')
                            ->label('Penjualan Tunai')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift?->summary ? number_format($this->currentShift->summary->cash_sales, 0, ',', '.') : '-'),

                        TextInput::make('summary_non_cash_sales')
                            ->label('Penjualan Non-Tunai')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift?->summary ? number_format($this->currentShift->summary->non_cash_sales, 0, ',', '.') : '-'),

                        TextInput::make('summary_refund_amount')
                            ->label('Refund')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift?->summary ? number_format($this->currentShift->summary->refund_amount, 0, ',', '.') : '-'),

                        TextInput::make('summary_expected_cash')
                            ->label('Ekspektasi Kas')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift?->summary ? number_format($this->currentShift->summary->expected_cash, 0, ',', '.') : '-'),

                        TextInput::make('summary_actual_cash')
                            ->label('Kas Fisik')
                            ->disabled()
                            ->prefix('Rp')
                            ->default(fn () => $this->currentShift?->summary ? number_format($this->currentShift->summary->actual_cash, 0, ',', '.') : '-'),

                        TextInput::make('summary_variance')
                            ->label('Selisih')
                            ->disabled()
                            ->prefix('Rp')
                            ->color(fn () => $this->currentShift?->summary && $this->currentShift->summary->variance > 0 ? 'success' : ($this->currentShift?->summary && $this->currentShift->summary->variance < 0 ? 'danger' : 'gray'))
                            ->default(fn () => $this->currentShift?->summary && $this->currentShift->summary->variance !== null ? number_format($this->currentShift->summary->variance, 0, ',', '.') : '-'),

                        Textarea::make('summary_variance_notes')
                            ->label('Catatan Selisih')
                            ->disabled()
                            ->default(fn () => $this->currentShift?->summary?->variance_notes ?? '-'),
                    ])
                    ->columns(3)
                    ->visible(fn () => $this->currentShift !== null && $this->currentShift->status === 'reconciled'),
            ])
            ->statePath('formData');
    }

    public function openShift(): void
    {
        $data = $this->form->getState();

        if (!$data['shift_definition_id']) {
            Notification::make()
                ->title('Error')
                ->body('Silakan pilih shift terlebih dahulu')
                ->danger()
                ->send();
            return;
        }

        // Calculate opening cash total
        $openingCash = 0;
        foreach ($data['opening_cash'] as $cash) {
            $openingCash += ($cash['denomination'] ?? 0) * ($cash['quantity'] ?? 0);
        }

        // Check if shift already exists for this date/user
        $existingShift = Shift::where('outlet_id', Auth::user()->outlet_id)
            ->where('user_id', Auth::id())
            ->where('shift_date', $data['shift_date'] ?? now())
            ->whereIn('status', ['opened', 'closed', 'reconciled'])
            ->first();

        if ($existingShift) {
            Notification::make()
                ->title('Error')
                ->body('Anda sudah memiliki shift untuk tanggal ini')
                ->danger()
                ->send();
            return;
        }

        $shift = Shift::create([
            'outlet_id' => Auth::user()->outlet_id,
            'shift_definition_id' => $data['shift_definition_id'],
            'user_id' => Auth::id(),
            'shift_date' => $data['shift_date'] ?? now(),
            'status' => 'opened',
            'opened_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        // Save opening cash details
        foreach ($data['opening_cash'] as $cash) {
            if (($cash['quantity'] ?? 0) > 0) {
                $shift->cashDetails()->create([
                    'type' => 'opening',
                    'denomination' => $cash['denomination'],
                    'quantity' => $cash['quantity'],
                ]);
            }
        }

        Notification::make()
            ->title('Shift Dibuka')
            ->body("Shift {$shift->shiftDefinition->name} berhasil dibuka dengan modal awal Rp " . number_format($openingCash, 0, ',', '.'))
            ->success()
            ->send();

        $this->loadCurrentShift();
        $this->form->fill($this->formData);
    }

    public function closeShift(): void
    {
        if (!$this->currentShift || $this->currentShift->status !== 'opened') {
            Notification::make()
                ->title('Error')
                ->body('Tidak ada shift yang bisa ditutup')
                ->danger()
                ->send();
            return;
        }

        $this->currentShift->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        Notification::make()
            ->title('Shift Ditutup')
            ->body('Shift berhasil ditutup. Silakan lakukan rekonsiliasi kas.')
            ->success()
            ->send();

        $this->loadCurrentShift();
        $this->form->fill($this->formData);
    }

    public function reconcileShift(): void
    {
        if (!$this->currentShift || $this->currentShift->status !== 'closed') {
            Notification::make()
                ->title('Error')
                ->body('Shift harus ditutup terlebih dahulu sebelum direkonsiliasi')
                ->danger()
                ->send();
            return;
        }

        $data = $this->form->getState();

        // Calculate actual cash from closing cash input
        $actualCash = 0;
        foreach ($data['closing_cash'] as $cash) {
            $subtotal = ($cash['denomination'] ?? 0) * ($cash['quantity'] ?? 0);
            $actualCash += $subtotal;

            if (($cash['quantity'] ?? 0) > 0) {
                $this->currentShift->cashDetails()->create([
                    'type' => 'closing',
                    'denomination' => $cash['denomination'],
                    'quantity' => $cash['quantity'],
                ]);
            }
        }

        // Calculate expected cash
        $openingCash = $this->currentShift->opening_cash;
        $cashSales = $this->currentShift->total_cash_sales;
        $refundAmount = $this->currentShift->refund_amount;
        $expectedCash = $openingCash + $cashSales - $refundAmount;
        $variance = $actualCash - $expectedCash;

        // Create or update summary
        $this->currentShift->summary()->updateOrCreate([], [
            'opening_cash' => $openingCash,
            'cash_sales' => $cashSales,
            'non_cash_sales' => $this->currentShift->total_non_cash_sales,
            'refund_amount' => $refundAmount,
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'variance_notes' => $data['variance_notes'] ?? null,
            'transaction_count' => $this->currentShift->transaction_count,
            'void_count' => $this->currentShift->void_count,
            'refund_count' => $this->currentShift->refund_count,
        ]);

        // Update shift status
        $this->currentShift->update([
            'status' => 'reconciled',
            'reconciled_at' => now(),
            'reconciled_by' => Auth::id(),
        ]);

        $message = 'Rekonsiliasi selesai. ';
        if ($variance > 0) {
            $message .= "Kas lebih Rp " . number_format($variance, 0, ',', '.');
        } elseif ($variance < 0) {
            $message .= "Kas kurang Rp " . number_format(abs($variance), 0, ',', '.');
        } else {
            $message .= 'Kas sesuai (selesai).';
        }

        Notification::make()
            ->title('Rekonsiliasi Berhasil')
            ->body($message)
            ->success()
            ->send();

        $this->loadCurrentShift();
        $this->form->fill($this->formData);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_shift')
                ->label('Buka Shift')
                ->icon('heroicon-o-play-circle')
                ->color('success')
                ->visible(fn () => $this->currentShift === null)
                ->action('openShift'),

            Action::make('close_shift')
                ->label('Tutup Shift')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->visible(fn () => $this->currentShift && $this->currentShift->status === 'opened')
                ->requiresConfirmation()
                ->modalHeading('Tutup Shift')
                ->modalDescription('Apakah Anda yakin ingin menutup shift ini? Pastikan semua transaksi sudah selesai.')
                ->modalSubmitActionLabel('Ya, Tutup Shift')
                ->action('closeShift'),

            Action::make('reconcile_shift')
                ->label('Rekonsiliasi Kas')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->visible(fn () => $this->currentShift && $this->currentShift->status === 'closed')
                ->action('reconcileShift'),

            Action::make('view_summary')
                ->label('Lihat Ringkasan')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->visible(fn () => $this->currentShift && $this->currentShift->status === 'reconciled')
                ->url(fn () => route('pos.shift-summary', $this->currentShift)),
        ];
    }

    public function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return 'full';
    }
}