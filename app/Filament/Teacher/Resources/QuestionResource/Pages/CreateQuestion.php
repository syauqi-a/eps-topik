<?php

namespace App\Filament\Teacher\Resources\QuestionResource\Pages;

use App\Filament\Teacher\Resources\QuestionResource;
use App\Models\Choice;
use App\Models\Question;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getResource()::getUrl();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Question created';
    }

    public static function getImagePath(string $content): ?array
    {
        $pattern = '/(images\/questions.{38}(xbm|tif|jfif|ico|tiff|gif|svg|webp|svgz|jpg|jpeg|png|bmp|pjp|apng|pjpeg|avif))/';
        if (preg_match_all($pattern, $content, $matches)) {
            return $matches[0];
        }
        return null;
    }

    protected function afterCreate()
    {
        $data = $this->data['ko_KR'];
        $record = $this->getRecord();
        $choices = $data['choices'];

        static::handlingAfterCreation($record, $choices);
    }

    public static function handlingAfterCreation(Question $record, array $choices): void
    {
        $record->update([
            'question_images' => static::getImagePath(
                $record->getTranslation('content', 'ko_KR')
            ),
            'created_by' => [
                'uid' => auth()->id(),
                'name' => auth()->user()->name
            ]
        ]);

        foreach ($choices as $choice) {
            if ($choice['is_image']) {
                $record->choices()->save(new Choice([
                    'type' => 'image',
                    'image' => reset($choice['image']),
                    'is_correct' => $choice['is_correct'],
                ]));
            } else {
                $record->choices()->save(new Choice([
                    'type' => 'text',
                    'text' => [
                        'ko_KR' => $choice['text'],
                    ],
                    'is_correct' => $choice['is_correct'],
                ]));
            }
        }
    }
}
