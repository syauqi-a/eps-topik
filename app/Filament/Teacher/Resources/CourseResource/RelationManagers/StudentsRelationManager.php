<?php

namespace App\Filament\Teacher\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Filament\Resources\RelationManagers\RelationManager;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->query(fn () => $this->getOwnerRecord()->students())
        ->inverseRelationship('student_has_courses')
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add Students')
                    ->modalHeading('Add Student')
                    ->recordSelect(function () {
                        return Forms\Components\Select::make('_id')
                            ->hiddenLabel()
                            ->placeholder('Select student')
                            ->options(function () {
                                $id = $this->getOwnerRecord()->getAttribute('_id');
                                return User::whereNot('student_course_ids', $id)
                                    ->pluck('name', '_id');
                            })
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->noSearchResultsMessage('No students found.')
                            ->native(false);
                    })
                    ->action(function (array $data, Table $table) {
                        $relationship = $table->getRelationship();
                        $relationship->attach($data['_id']);
                    })
                    ->attachAnother(false),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Kick out')
                    ->modalHeading('Remove selected students')
                    ->action(function (
                        Tables\Actions\DetachBulkAction $action,
                        Collection $records,
                        Table $table
                    ) {
                        $relationship = $table->getRelationship();
                        $records->each(function (Model $record) use ($action, $relationship) {
                            $relationship->detach($record);
                            $action->success();
                        });
                    }),
            ]);
    }
}
