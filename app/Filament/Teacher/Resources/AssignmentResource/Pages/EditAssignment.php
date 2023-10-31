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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['deadlines'] = [
            'starts' => $data['starts'] ? CreateAssignment::createDatetime($data['starts']): null,
            'ends' => $data['ends'] ? CreateAssignment::createDatetime($data['ends']): null,
        ];

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Assignment updated';
    }
}
