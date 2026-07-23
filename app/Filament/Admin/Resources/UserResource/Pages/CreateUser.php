<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Staff berhasil ditambahkan')
            ->body('Data staff baru telah disimpan.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default outlet_id if not super_admin
        if (auth()->user()->role !== 'super_admin' && empty($data['outlet_id'])) {
            $data['outlet_id'] = auth()->user()->outlet_id;
        }

        // Set default role if not super_admin/manager
        if (!in_array(auth()->user()->role, ['super_admin', 'manager']) && empty($data['role'])) {
            $data['role'] = 'kasir';
        }

        return $data;
    }
}