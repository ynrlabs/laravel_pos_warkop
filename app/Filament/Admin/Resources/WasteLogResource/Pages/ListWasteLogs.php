<?php

namespace App\Filament\Admin\Resources\WasteLogResource\Pages;

use App\Filament\Admin\Resources\WasteLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWasteLogs extends ListRecords
{
    protected static string $resource = WasteLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}