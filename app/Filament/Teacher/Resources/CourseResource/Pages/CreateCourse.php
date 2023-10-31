<?php

namespace App\Filament\Teacher\Resources\CourseResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Teacher\Resources\CourseResource;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = [
            '_id' => auth()->id(),
            'name' => auth()->user()->name
        ];
        return $data;
    }

    protected function afterCreate()
    {
        $record = $this->getRecord();
        $record->teachers()->attach($record->created_by['_id']);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Course created';
    }
}
