<?php

namespace App\Filament\Admin\Resources\ShiftResource\Pages;

use App\Filament\Admin\Resources\ShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateShift extends CreateRecord
{
    protected static string $resource = ShiftResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Shift berhasil dibuka';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['opened_at'] = now();
        $data['status'] = 'opened';
        return $data;
    }
}