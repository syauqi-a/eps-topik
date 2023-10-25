<?php

namespace App\Filament\Teacher\Resources\CourseResource\Pages;

use App\Filament\Teacher\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_id'] = auth()->id();
        return $data;
    }

    protected function afterCreate()
    {
        $record = $this->getRecord();
        $record->teachers()->attach($record->created_by_id);
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Course created';
    }
}
