<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AssignmentResource\Pages;
use App\Filament\Teacher\Resources\AssignmentResource as TeacherAssignmentResource;
use App\Models\Assignment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return TeacherAssignmentResource::getCustomTable($table)
            ->query(fn () => Assignment::where('student_ids', auth()->id())
                ->orWhereIn(
                    'course_ids',
                    auth()->user()->student_has_courses()->pluck('_id')
                ))
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAssignments::route('/'),
            'view' => Pages\ViewAssignment::route('/{record}'),
        ];
    }    
}
