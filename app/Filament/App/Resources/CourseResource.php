<?php

namespace App\Filament\App\Resources;

use Filament\Tables;
use App\Models\Course;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\App\Resources\CourseResource\Pages;
use App\Filament\Teacher\Resources\CourseResource\RelationManagers;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Study';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 15;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => auth()->user()->student_has_courses())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_by.name')
                    ->label('Teacher')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Leave')
                    ->modalHeading('Drop out of the course')
                    ->action(function (
                        Tables\Actions\DetachAction $action,
                        Course $record
                    ) {
                        $record->students()->detach(auth()->id());
                        $action->successNotificationTitle(
                            'Dropped out of the course'
                        )->success();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Leave selected')
                    ->modalHeading('Drop out of the courses')
                    ->action(function (
                        Tables\Actions\DetachBulkAction $action,
                        Collection $records
                    ) {
                        $uid = auth()->id();
                        $records->each(function (Model $record) use ($uid) {
                            $record->students()->detach($uid);
                        });
                        $action->successNotificationTitle(
                            'Dropped out of the course'
                        )->success();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AssignmentsRelationManager::class,
            RelationManagers\StudentsRelationManager::class,
            RelationManagers\TeachersRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCourses::route('/'),
            'view' => Pages\ViewCourse::route('/{record}'),
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Owner'=> $record->created_by['name'],
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->where(function ($query) {
                $uid = auth()->id();
                $query->where('is_private', false)
                      ->orWhere('teacher_ids', $uid)
                      ->orWhere('student_ids', $uid);
            });
    }
}
