<?php

namespace App\Filament\Teacher\Resources\QuestionResource\Pages;

use App\Filament\Teacher\Resources\QuestionResource;
use App\Models\Choice;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = [
            'uid' => auth()->id(),
            'name' => auth()->user()->name
        ];
        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->data;

        $record = $this->getRecord();

        foreach ($data['choices'] as $choice) {
            if ($choice['text']) {
                $record->choices()->save(new Choice([
                    'text' => $choice['text'],
                    'is_correct' => $choice['is_correct'],
                ]));
            } else {
                $record->choices()->save(new Choice([
                    'image' => reset($choice['image']),
                    'is_correct' => $choice['is_correct'],
                ]));
            }
            
        }
    }
}
