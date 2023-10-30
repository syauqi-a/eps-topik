<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use Closure;
use Filament\Forms;
use Filament\Actions;
use App\Models\Course;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\HtmlString;
use MongoDB\Laravel\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\App\Resources\CourseResource;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected static string $view = 'filament.resources.courses.pages.view';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make([
                    Infolists\Components\TextEntry::make('name'),
                    Infolists\Components\TextEntry::make('description'),
                    Infolists\Components\TextEntry::make('is_private')
                        ->label('Course accessibility')
                        ->formatStateUsing(fn ($state) => $state ? 'Private' : 'Public'),
                    Infolists\Components\TextEntry::make('created_by.name')
                        ->label('Course owner'),
                    Infolists\Components\TextEntry::make('teachers')
                        ->label('Number of teachers')
                        ->getStateUsing(fn (Model $record) => $record->teachers()->count()),
                    Infolists\Components\TextEntry::make('students')
                        ->label('Number of students')
                        ->getStateUsing(fn (Model $record) => $record->students()->count()),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Join this course')
                ->form([
                    Forms\Components\TextInput::make('course_key')
                        ->password()
                        ->required()
                        ->hidden(fn (Course $record) => !$record->is_private)
                        ->rule(static function (Course $record) {
                            return static function (string $attribute, $value, Closure $fail) use ($record) {
                                $valid_key = $record->course_key ?? null;
                                if ($valid_key && $value != $valid_key) {
                                    $fail('The course key you entered is incorrect.');
                                }
                            };
                        }),
                ])
                ->requiresConfirmation()
                ->action(function (Course $record) {
                    $record->students()->attach(auth()->id());
                    Notification::make()
                        ->success()
                        ->title('Successfully joined the course')
                        ->send();
                })
                ->hidden(function (Course $record) {
                    return in_array(auth()->id(), $record->student_ids);
                }),
            Actions\Action::make('Leave this course')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Drop out of the course')
                ->action(function (Course $record) {
                    $record->students()->detach(auth()->id());
                    Notification::make()
                        ->success()
                        ->title('Dropped out of the course')
                        ->send();
                })
                ->hidden(function (Course $record) {
                    return in_array(auth()->id(), $record->student_ids) == false;
                }),
            Actions\Action::make('course_link')
                ->color('info')
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
                ->action(function () {
                    Notification::make('copy_course_link')
                        ->success()
                        ->title('Copied to clipboard')
                        ->send();
                })
                ->hidden(function (Course $record) {
                    return in_array(auth()->id(), $record->student_ids) == false;
                }),
        ];
    }
}
