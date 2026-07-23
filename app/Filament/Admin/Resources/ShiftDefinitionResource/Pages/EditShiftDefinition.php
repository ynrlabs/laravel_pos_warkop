<?php

namespace App\Filament\Admin\Resources\ShiftDefinitionResource\Pages;

use App\Filament\Admin\Resources\ShiftDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShiftDefinition extends EditRecord
{
    protected static string $resource = ShiftDefinitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}