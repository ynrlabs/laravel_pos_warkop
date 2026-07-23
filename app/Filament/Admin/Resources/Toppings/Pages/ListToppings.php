<?php

namespace App\Filament\Admin\Resources\Toppings\Pages;

use App\Filament\Admin\Resources\Toppings\ToppingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListToppings extends ListRecords
{
    protected static string $resource = ToppingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
