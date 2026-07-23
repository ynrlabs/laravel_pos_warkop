<?php

namespace App\Filament\Admin\Resources\ShiftDefinitionResource\Pages;

use App\Filament\Admin\Resources\ShiftDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateShiftDefinition extends CreateRecord
{
    protected static string $resource = ShiftDefinitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}