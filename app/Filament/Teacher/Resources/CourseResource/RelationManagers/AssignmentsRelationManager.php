<?php

namespace App\Filament\Teacher\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Assignment;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Teacher\Resources\AssignmentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Teacher\Resources\AssignmentResource\Pages\CreateAssignment;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    public function form(Form $form): Form
    {
        return AssignmentResource::form($form);
    }

    public function table(Table $table): Table
    {
        return AssignmentResource::getCustomTable($table)
            ->query(fn () => $this->getOwnerRecord()->assignments())
            ->recordTitleAttribute('name')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->tooltip('Add a new assignment')
                    ->mutateFormDataUsing(function (array $data): array {
                        return CreateAssignment::customMutateBeforeCreate($data);
                    })
                    ->closeModalByClickingAway(false),
                Tables\Actions\AttachAction::make()
                    ->color(Color::Emerald)
                    ->label('Add Assignments')
                    ->tooltip('Add assignments that have been created')
                    ->modalHeading('Add Assignments')
                    ->recordSelect(function () {
                        return Forms\Components\Select::make('_id')
                            ->hiddenLabel()
                            ->placeholder('Select assignments')
                            ->options(function () {
                                $coures_ids = $this->getOwnerRecord()->getAttribute('_id');
                                $uid = auth()->id();
                                return Assignment::whereNot('course_ids', $coures_ids)
                                    ->where('created_by.uid', $uid)
                                    ->pluck('name', '_id');
                            })
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->noSearchResultsMessage('No assignments found.')
                            ->native(false);
                    })
                    ->action(function (array $data, Table $table) {
                        $relationship = $table->getRelationship();
                        $relationship->attach($data['_id']);
                    })
                    ->attachAnother(false)
                    ->closeModalByClickingAway(false)
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(
                        fn (Assignment $record) => route(
                            'filament.teacher.resources.assignments.edit',
                            $record
                        ),
                        true
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Unassignment')
                    ->modalHeading('Remove selected assignments')
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
