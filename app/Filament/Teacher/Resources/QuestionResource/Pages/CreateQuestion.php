<?php

namespace App\Filament\Teacher\Resources\QuestionResource\Pages;

use App\Filament\Teacher\Resources\QuestionResource;
use App\Models\Assignment;
use App\Models\Choice;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getResource()::getUrl();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Question created';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = [
            'uid' => auth()->id(),
            'name' => auth()->user()->name
        ];
        return $data;
    }

    protected function afterCreate()
    {
        $data = $this->data;

        $record = $this->getRecord();

        foreach ($data['choices'] as $choice) {
            if ($choice['is_image']) {
                $record->choices()->save(new Choice([
                    'type' => 'image',
                    'image' => reset($choice['image']),
                    'is_correct' => $choice['is_correct'],
                ]));
            } else {
                $record->choices()->save(new Choice([
                    'type' => 'text',
                    'text' => $choice['text'],
                    'is_correct' => $choice['is_correct'],
                ]));
            }
            
        }
        if (array_key_exists('assignment_id', $this->data)) {
            if (Assignment::where('_id', $this->data['assignment_id'])->first()) {
                $record->assignments()->attach($this->data['assignment_id']);
                Notification::make('success_assigned')
                    ->success()
                    ->title('Successfully assigned question')
                    ->send();
            } else {
                Notification::make('fail_assigned')
                    ->warning()
                    ->title('Failed to assign question')
                    ->body('Assignment ID not found')
                    ->send();
            }
        }
    }

    protected function afterFill()
    {
        if (key_exists('assign_to', $_GET) && $_GET['assign_to']) {
            $this->data['assignment_id'] = $_GET['assign_to'];
        }
    }
}
