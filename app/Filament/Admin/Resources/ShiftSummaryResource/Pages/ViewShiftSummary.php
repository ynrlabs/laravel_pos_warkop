<?php

namespace App\Filament\Admin\Resources\ShiftSummaryResource\Pages;

use App\Filament\Admin\Resources\ShiftSummaryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewShiftSummary extends ViewRecord
{
    protected static string $resource = ShiftSummaryResource::class;

    public function getTitle(): string
    {
        return 'Ringkasan Shift - ' . $this->record->shift->shift_date->format('d M Y') . ' ' . $this->record->shift->shiftDefinition->name;
    }
}