<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use Filament\Infolists;
use Filament\Infolists\Infolist;
use MongoDB\Laravel\Eloquent\Model;
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
}
