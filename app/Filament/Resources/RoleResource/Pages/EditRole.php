<?php

namespace App\Filament\Resources\RoleResource\Pages;

use Filament\Actions\DeleteAction;
use Maklad\Permission\Models\Role;
use App\Filament\Resources\RoleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action, Role $record) {
                    if (in_array($record->name, $record->prevent_deleting)) {
                        Notification::make()
                            ->warning()
                            ->title('Failed to delete!')
                            ->body("You cannot delete the \"{$record->name}\" role.")
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }
                }
            ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Role updated';
    }
}
