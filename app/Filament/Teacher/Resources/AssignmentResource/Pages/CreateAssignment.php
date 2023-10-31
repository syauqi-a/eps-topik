<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use MongoDB\BSON\UTCDateTime;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Teacher\Resources\AssignmentResource;
use Carbon\Carbon;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function createDatetime($datetime) {
        $date = new Carbon($datetime);
        $mongo_date = new UTCDateTime($date->format('U') * 1000);
        return $mongo_date;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['deadlines'] = [
            'starts' => $data['starts'] ? $this->createDatetime($data['starts']): null,
            'ends' => $data['ends'] ? $this->createDatetime($data['ends']): null,
        ];

        $data['created_by'] = [
            '_id' => auth()->id(),
            'name' => auth()->user()->name,
        ];

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Assignment created';
    }
}
