<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Teacher\Resources\AssignmentResource;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['timezone'] ??= 'Asia/Jakarta';
        if ($data['is_unlimited'] === false) {
            $data['starts'] = $data['deadlines']['starts'];
            $data['ends'] = $data['deadlines']['ends'];
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getResource()::getUrl();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['deadlines'] = [
            'starts' => CreateAssignment::createDatetime(
                array_key_exists('starts', $data) ? $data['starts'] : '',
                $data['is_unlimited']
            ),
            'ends' => CreateAssignment::createDatetime(
                array_key_exists('ends', $data) ? $data['ends'] : '',
                $data['is_unlimited']
            ),
        ];

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Assignment updated';
    }
}
