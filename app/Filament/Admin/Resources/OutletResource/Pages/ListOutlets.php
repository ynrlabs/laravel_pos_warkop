<?php

namespace App\Filament\Admin\Resources\OutletResource\Pages;

use App\Filament\Admin\Resources\OutletResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutlets extends ListRecords
{
    protected static string $resource = OutletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}