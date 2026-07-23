<?php

namespace App\Filament\Admin\Resources\ShiftDefinitionResource\Pages;

use App\Filament\Admin\Resources\ShiftDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShiftDefinitions extends ListRecords
{
    protected static string $resource = ShiftDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Shift Definition'),
        ];
    }
}