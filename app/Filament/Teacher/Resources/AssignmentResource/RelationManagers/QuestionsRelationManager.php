<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\RelationManagers;

use Filament\Tables;
use App\Models\Choice;
use App\Models\Question;
use Filament\Forms\Form;
use Filament\Tables\Table;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Teacher\Resources\QuestionResource;
use Filament\Resources\RelationManagers\RelationManager;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Form $form): Form
    {
        return QuestionResource::getCustomForm($form);
    }

    public function table(Table $table): Table
    {
        return QuestionResource::getCustomTable($table)
            ->query(fn () => $this->getOwnerRecord()->questions())
            ->recordTitleAttribute('content')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->before(function (array $data) {
                        $data['created_by'] = [
                            'uid' => auth()->id(),
                            'name' => auth()->user()->name
                        ];
                        return $data;
                    })
                    ->after(function (array $data, Question $record) {
                        foreach ($data['choices'] as $choice) {
                            if ($choice['text']) {
                                $record->choices()->save(new Choice([
                                    'text' => $choice['text']
                                ]));
                            } else {
                                $record->choices()->save(new Choice([
                                    'image' => reset($choice['image'])
                                ]));
                            }

                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make('ubah')
                        ->url(fn (Question $record) => route(
                            'filament.teacher.resources.questions.edit',
                            $record
                        ), true),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove selected questions from list')
                    ->action(function (
                        Tables\Actions\DetachBulkAction $action,
                        Collection $records,
                        Table $table
                    ) {
                        $relationship = $table->getRelationship();
                        $records->each(function (Model $record) use ($action, $relationship) {
                            $relationship->detach($record);
                            $action->successNotificationTitle('Removed')
                                ->success();
                        });
                    }),
            ]);
    }
}
