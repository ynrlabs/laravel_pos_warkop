<?php

namespace App\Filament\Admin\Resources\Toppings\Pages;

use App\Filament\Admin\Resources\Toppings\ToppingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTopping extends EditRecord
{
    protected static string $resource = ToppingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
