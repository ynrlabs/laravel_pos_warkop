<?php

namespace App\Filament\Admin\Resources\OutletResource\Pages;

use App\Filament\Admin\Resources\OutletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutlet extends EditRecord
{
    protected static string $resource = OutletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}