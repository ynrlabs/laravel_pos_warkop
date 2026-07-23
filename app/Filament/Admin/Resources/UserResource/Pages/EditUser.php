<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data staff diperbarui')
            ->body('Perubahan telah disimpan.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Prevent non-super_admin from changing outlet
        if (auth()->user()->role !== 'super_admin') {
            unset($data['outlet_id']);
        }

        // Prevent non-super_admin/manager from changing role
        if (!in_array(auth()->user()->role, ['super_admin', 'manager'])) {
            unset($data['role']);
        }

        // Prevent manager from changing super_admin/manager roles
        if (auth()->user()->role === 'manager' && $this->record) {
            if (in_array($this->record->role, ['super_admin', 'manager'])) {
                unset($data['role']);
            }
        }

        return $data;
    }
}