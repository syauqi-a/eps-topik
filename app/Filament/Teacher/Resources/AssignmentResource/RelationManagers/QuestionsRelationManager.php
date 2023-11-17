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
use App\Filament\Teacher\Resources\QuestionResource\Pages\CreateQuestion;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\Concerns\Translatable;
use Illuminate\Support\HtmlString;
use League\CommonMark\GithubFlavoredMarkdownConverter as Converter;

class QuestionsRelationManager extends RelationManager
{
    use Translatable;

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
                Tables\Actions\LocaleSwitcher::make(),
                Tables\Actions\CreateAction::make()
                    ->modalHeading(fn () => 'Create question #' .
                        count($this->getOwnerRecord()->question_ids ?? []) + 1)
                    ->tooltip('Add a new assignment')
                    ->closeModalByClickingAway(false)
                    ->after(function (Question $record) {
                        CreateQuestion::handlingAfterCreation(
                            $record,
                            $this->mountedTableActionsData[0]['choices']
                        );
                    }),
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
                Tables\Actions\Action::make('reorder')
                    ->icon('heroicon-s-arrows-up-down')
                    ->iconButton()
                    ->label('Reorder Question')
                    ->tooltip('Reorder Question')
                    ->hidden(fn (Table $table) => $table->getRecords()->total() == 0)
                    ->fillForm(function (QuestionsRelationManager $livewire) {
                        $question_ids = $livewire->getOwnerRecord()->question_ids;
                        $data = array(
                            'questions' => array(),
                        );

                        foreach ($question_ids as $id) {
                            $data['questions'][] = Question::where('_id', $id)
                                ->first();
                        }

                        return $data;
                    })
                    ->form([
                        Forms\Components\Repeater::make('questions')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Placeholder::make('content')
                                    ->hiddenLabel()
                                    ->content(fn ($state) => new HtmlString(
                                        '<b>Korean</b>:<br/>' . $state['ko_KR'] .
                                        (array_key_exists('id', $state) ? '<br/><b>Indonesian</b>:<br/>' . $state['id'] : '')
                                    )),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderableWithButtons()
                            ->itemLabel(function (array $state) {
                                $content = $state['content'];
                                return $content
                                    ? Str::limit(strip_tags(
                                        imgTagsToEmoji($content['ko_KR'], ' image')
                                    ), 75)
                                    : null;
                            })
                            ->collapsed()
                            ->expandAction(fn () => null),
                    ])
                    ->action(function (array $data) {
                        $question_ids = array();

                        foreach ($data['questions'] as $question) {
                            $question_ids[] = $question['_id'];
                        }

                        $assignment = $this->getOwnerRecord();
                        $assignment->update([
                            'question_ids' => $question_ids,
                        ]);
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
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
                    ->modalDescription(new HtmlString(
                        'Are you sure you would like to do this?<br/><br/><b>NB</b>: <i>Questions will <b>only be removed</b> from the assignment list but not deleted.</i>'
                    ))
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
        $record = $this->getOwnerRecord();

        if ($record->created_by['uid'] === auth()->id()) {
            return false;
        }

        $courses = $record->courses()->pluck('_id')->toArray();;
        $my_courses = auth()->user()->teacher_has_courses()->pluck('_id')->toArray();;

        if (empty(array_intersect($courses, $my_courses))) {
            return true;
        }

        return false;
    }
}
