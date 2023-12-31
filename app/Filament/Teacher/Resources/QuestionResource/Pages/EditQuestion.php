<?php

namespace App\Filament\Teacher\Resources\QuestionResource\Pages;

use App\Filament\Teacher\Resources\QuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestion extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function afterSave()
    {
        $record = $this->getRecord();
        $content = $this->data['ko_KR']['content'];

        $record->update([
            'question_images' => CreateQuestion::getImagePath($content),
        ]);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getResource()::getUrl();
    }
}
