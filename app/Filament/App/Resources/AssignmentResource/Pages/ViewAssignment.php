<?php

namespace App\Filament\App\Resources\AssignmentResource\Pages;

use DateTimeZone;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use MongoDB\Laravel\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\App\Resources\AssignmentResource;
use Filament\Support\Colors\Color;

class ViewAssignment extends ViewRecord
{
    protected static string $resource = AssignmentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make([
                    Infolists\Components\TextEntry::make('name'),
                    Infolists\Components\TextEntry::make('description')
                        ->formatStateUsing(fn ($state) => wordwrap($state, 40, "\n", true)),
                    Infolists\Components\TextEntry::make('is_unlimited')
                        ->label('Deadlines')
                        ->visible(fn ($state) => $state)
                        ->formatStateUsing(fn () => 'Unlimited'),
                    Infolists\Components\TextEntry::make('deadlines.starts')
                        ->label('Starting time')
                        ->hidden(fn (Model $record) => $record->is_unlimited)
                        ->formatStateUsing(fn ($state, Model $record) => $state
                            ->toDateTime()
                            ->setTimeZone(new DateTimeZone($record->timezone))
                            ->format('r')),
                    Infolists\Components\TextEntry::make('deadlines.ends')
                        ->label('Deadlines')
                        ->hidden(fn (Model $record) => $record->is_unlimited)
                        ->formatStateUsing(fn ($state, Model $record) => $state
                            ->toDateTime()
                            ->setTimeZone(new DateTimeZone($record->timezone))
                            ->format('r')),
                    Infolists\Components\TextEntry::make('created_by.name')
                        ->label('Teacher'),
                    Infolists\Components\RepeatableEntry::make('courses')
                        ->getStateUsing(function (Model $record) {
                            $user_courses = auth()->user()
                                ->student_has_courses()
                                ->pluck('_id');
    
                            return $record->courses()
                                ->whereIn('_id', $user_courses)
                                ->get();
                        })
                        ->label('The courses assigns it')
                        ->schema([
                            Infolists\Components\TextEntry::make('name')
                                ->hiddenLabel()
                                ->suffixAction(
                                    Infolists\Components\Actions\Action::make('view')
                                        ->label('View course')
                                        ->icon('heroicon-o-document-magnifying-glass')
                                        ->iconSize('lg')
                                        ->url(fn (Model $record) => route(
                                            'filament.app.resources.courses.view',
                                            $record->getAttribute('_id')
                                        ))
                                )
                        ])
                        ->grid(3)
                        ->hidden(fn ($state) => $state === null)
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public function getRelationManagers(): array
    {
        if (auth()->user()->isTeacher()) {
            return parent::getRelationManagers();
        }

        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('take_exam')
                ->color(Color::Rose)
                ->icon('heroicon-o-rocket-launch')
                ->tooltip('Take individual exam')
                ->hidden(function (Model $record) {
                    $uid = auth()->id();
                    $students = $record->getAttribute('student_ids') ?? [];

                    $courses = $record->courses()->get();
                    foreach ($courses as $course) {
                        $students = array_merge($students, $course->student_ids);
                    }

                    if ($students && in_array($uid, $students)) {
                        return false;
                    }

                    return true;
                })
                ->url(fn (Model $record) => route(
                    'filament.app.resources.assignments.exam',
                    $record->getAttribute('_id')
                )),
            Actions\Action::make('leaderboard')
                ->color(Color::Yellow)
                ->icon('heroicon-o-trophy')
                ->tooltip('View leaderboard')
                ->hidden(function (Model $record) {
                    $uid = auth()->id();
                    $students = $record->getAttribute('student_ids') ?? [];

                    $courses = $record->courses()->get();
                    foreach ($courses as $course) {
                        $students = array_merge($students, $course->student_ids);
                    }

                    if ($students && in_array($uid, $students)) {
                        return false;
                    }

                    return true;
                }),
        ];
    }
}
