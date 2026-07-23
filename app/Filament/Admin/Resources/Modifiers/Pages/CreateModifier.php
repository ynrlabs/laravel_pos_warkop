<?php

namespace App\Filament\Admin\Resources\Modifiers\Pages;

use App\Filament\Admin\Resources\Modifiers\ModifierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateModifier extends CreateRecord
{
    protected static string $resource = ModifierResource::class;
}
