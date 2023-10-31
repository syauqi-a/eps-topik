<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Teacher\Resources\AssignmentResource;

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
        if ($data['unlimited']) {
            $data['deadlines'] = [
                'starts' => null,
                'ends' => null,
            ];
        } else {
            $data['deadlines'] = [
                'starts' => $this->createDatetime($data['starts']),
                'ends' => $this->createDatetime($data['ends']),
            ];
        }

        $data['created_by'] = [
            '_id' => auth()->id(),
            'name' => auth()->user()->name,
        ];

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Assignment created';
    }
}
