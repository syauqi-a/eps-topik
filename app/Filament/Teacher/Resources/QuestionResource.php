<?php

namespace App\Filament\Teacher\Resources;

use Filament\Forms;
use Filament\Tables;
use Livewire\Component as Livewire;
use Filament\Forms\Get;
use App\Models\Question;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Forms\Components\PreviewHtml;
use App\Filament\Teacher\Resources\QuestionResource\Pages;
use App\Filament\Teacher\Resources\QuestionResource\RelationManagers;
use App\Filament\Teacher\Resources\QuestionResource\RelationManagers\ChoicesRelationManager;
use League\CommonMark\GithubFlavoredMarkdownConverter as Converter;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    public static function form(Form $form): Form
    {
        return static::getQuestionForm($form);
    }

    public static function table(Table $table): Table
    {
        return static::getQuestionTable($table)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }

    public static function getQuestionForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\MarkdownEditor::make('content')
                        ->required()
                        ->disableToolbarButtons(['attachFiles'])
                        ->live(debounce: 300)
                        ->columnSpan(fn (Livewire $livewire) => str_contains($livewire->getName(), 'relation-manager') ? 'full' : 1),
                    PreviewHtml::make('Preview')
                        ->hidden(fn (Livewire $livewire) => str_contains($livewire->getName(), 'relation-manager')),
                    Forms\Components\Select::make('question_type')
                        ->options(Question::questionTypes())
                        ->required()
                        ->native(false)
                        ->live(),
                    Forms\Components\TagsInput::make('tags')
                        ->suggestions(Question::tags())
                        ->required(),
                    Forms\Components\FileUpload::make('question_image')
                        ->image()
                        ->imageEditor()
                        // ->disk('s3')
                        ->directory('images/questions')
                        ->visibility('public'),
                    Forms\Components\FileUpload::make('question_audio')
                        ->acceptedFileTypes(['audio/*'])
                        // ->disk('s3')
                        ->directory('audios/questions')
                        ->visibility('public')
                        ->visible(fn (Get $get) => $get('question_type') === '듣기')
                        ->required(),
                ])->columns(2),
                Forms\Components\Section::make([
                    Forms\Components\Repeater::make('choices')
                        ->schema(ChoicesRelationManager::getChoiceForm())
                        ->grid(2)
                        ->collapsible()
                        ->itemLabel(function (array $state): ?string {
                            if ($state['text']) {
                                $badge = $state['is_correct'] ? '✔' : '❌';
                                return $state['text'] . ' ' . $badge;
                            } else {
                                return null;
                            }
                        })
                        ->defaultItems(4),
                ])->hiddenOn('edit'),
            ]);
    }

    public static function getQuestionTable(Table $table): Table
    {
        return $table
            ->query(fn () => Question::where('created_by.uid', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('content')
                    ->limit(50)
                    ->wrap()
                    ->formatStateUsing(fn ($state) => strip_tags(
                        (new Converter())->convert($state)->getContent()
                    )),
                Tables\Columns\TextColumn::make('question_type')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('tags')
                    ->badge(),
                Tables\Columns\TextColumn::make('question_image')
                    ->label('Image')
                    ->icon(fn ($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->badge()
                    ->default(false)
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('question_audio')
                    ->label('Audio')
                    ->icon(fn ($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->badge()
                    ->default(false)
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->filters([
                //
            ]);
    }
}
