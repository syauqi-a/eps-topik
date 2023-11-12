<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AssignmentResource\Pages;
use App\Filament\Teacher\Resources\AssignmentResource as TeacherAssignmentResource;
use App\Filament\Teacher\Resources\AssignmentResource\RelationManagers\QuestionsRelationManager;
use App\Models\Assignment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Study';

    // Labels
    protected static ?string $navigationLabel = 'My Assignments';
    protected static ?string $breadcrumb = 'My Assignments';
    protected static ?string $pluralModelLabel = 'My Assignments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return TeacherAssignmentResource::getAssignmentTable($table)
            ->query(fn () => Assignment::where('student_ids', auth()->id())
                ->orWhereIn(
                    'course_ids',
                    auth()->user()->student_has_courses()->pluck('_id')
                ))
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAssignments::route('/'),
            'view' => Pages\ViewAssignment::route('/{record}'),
            'exam' =>Pages\TakeExam::route('/{record}/exam'),
            // 'leaderboard' =>Pages\Leaderboard::route('/{record}/leaderboard'),
        ];
    }
}
