<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Question;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Support\Colors\Color;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Teacher\Resources\QuestionResource;
use Filament\Resources\RelationManagers\RelationManager;
use League\CommonMark\GithubFlavoredMarkdownConverter as Converter;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Form $form): Form
    {
        return QuestionResource::getQuestionForm($form);
    }

    public function table(Table $table): Table
    {
        return QuestionResource::getQuestionTable($table)
            ->query(fn () => $this->getOwnerRecord()->questions())
            ->recordTitleAttribute('content')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->tooltip('Add a new assignment'),
                Tables\Actions\AttachAction::make()
                    ->color(Color::Emerald)
                    ->label('Add Questions')
                    ->tooltip('Add questions that have been created')
                    ->modalHeading('Add Questions')
                    ->recordSelect(function () {
                        return Forms\Components\Select::make('_id')
                            ->hiddenLabel()
                            ->placeholder('Select questions')
                            ->options(function () {
                                $assignment_ids = $this->getOwnerRecord()->getAttribute('_id');
                                $uid = auth()->id();
                                return Question::whereNot('assignment_ids', $assignment_ids)
                                    ->where('created_by.uid', $uid)
                                    ->get()
                                    ->mapWithKeys(function ($question) {
                                        $raw = json_decode(
                                            $question->getAttributes()['content'],
                                            true
                                        );

                                        return [
                                            $question['_id'] => Str::limit(strip_tags(
                                                (new Converter())->convert($raw['ko_KR'])->getContent()
                                            ), 50)
                                        ];
                                    });
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
                    ->closeModalByClickingAway(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('Edit')
                    ->url(fn (Question $record) => route(
                        'filament.teacher.resources.questions.edit',
                        $record
                    )),
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

    public function isReadOnly(): bool
    {
        $courses = $this->getOwnerRecord()->courses()->pluck('_id')->toArray();;
        $my_courses = auth()->user()->teacher_has_courses()->pluck('_id')->toArray();;

        if (empty(array_intersect($courses, $my_courses))) {
            return true;
        }

        return false;
    }
}
