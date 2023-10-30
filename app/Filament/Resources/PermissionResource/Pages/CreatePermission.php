<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Models\Permission;
use Illuminate\Support\Str;
use MongoDB\Laravel\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PermissionResource;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Permission created';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $added_amount = 0;
        $added_name = array();

        $record = new ($this->getModel())($data);
        if ($data['name']) {
            $record->save();
            $added_amount += 1;
            $added_name[] = $data['name'];
        }

        if ($data['model_name']) {
            foreach ($data['permissions'] as $permission) {
                $name = Str::lower($permission . " " . (Str::plural($data['model_name'])));
                if (count(Permission::where('name', $name)->get()) == 0) {
                    Permission::create(['name' => $name]);
                    $added_amount += 1;
                    $added_name[] = $name;
                }
            }
        }

        $added_name = join("\", \"", $added_name);
        $notif = Notification::make()
            ->title(
                ($added_amount) ? "Added {$added_amount} Permissions" : 'Failed to add!'
            )
            ->body(
                ($added_amount) ? "Added Permissions for \"{$added_name}\"" : 'All Permissions already exist in the database'
            )
            ->success();

        (($added_amount) ? $notif->success():$notif->warning())->send();

        return $record;
    }
}
