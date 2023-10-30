<?php

namespace App\Filament\Teacher\Resources\CourseResource\Pages;

use Filament\Actions;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Teacher\Resources\CourseResource;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('course_link')
                ->icon('heroicon-m-clipboard-document')
                ->tooltip('Copy course link to clipboard')
                ->extraAttributes(function () {
                    $link = route('course.join', $this->data['_id']);
                    return [
                        'onclick' => new HtmlString(
                            '{(() => {' .
                                'var tempItem = document.createElement(\'input\');' .
                                'tempItem.setAttribute(\'display\',\'none\');' .
                                'tempItem.setAttribute(\'value\',\''.$link.'\');' .
                                'document.body.appendChild(tempItem);' .
                                'tempItem.select();' .
                                'document.execCommand(\'Copy\');' .
                                'tempItem.parentElement.removeChild(tempItem);' .
                            '})()}'
                        ),
                    ];
                })
                ->action(function () {
                    Notification::make('copy_course_link')
                        ->title('Copied to clipboard')
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Course updated';
    }
}
