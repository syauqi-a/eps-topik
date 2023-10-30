<?php

namespace App\Filament\Teacher\Resources\CourseResource\Pages;

use Filament\Actions;
use App\Models\Course;
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
                ->extraAttributes(function (Course $record) {
                    $link = route('course.join', $record['_id']);
                    if ($record->is_private && $record->course_key) {
                        $link .= '?course_key='.$record->course_key;
                    }
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
                ->action(function (Actions\Action $action, Course $record) {
                    if ($record->is_private && empty($record->course_key)) {
                        Notification::make('fail_copy_course_link')
                            ->warning()
                            ->title('Set a course key first for Private course')
                            ->send();
                        $action->cancel();
                    } else {
                        Notification::make('copy_course_link')
                            ->success()
                            ->title('Copied to clipboard')
                            ->send();
                    }
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
