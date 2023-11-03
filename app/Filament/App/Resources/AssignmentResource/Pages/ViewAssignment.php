<?php

namespace App\Filament\App\Resources\AssignmentResource\Pages;

use DateTimeZone;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use MongoDB\Laravel\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\App\Resources\AssignmentResource;

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
                                        ->iconSize('l')
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
}
