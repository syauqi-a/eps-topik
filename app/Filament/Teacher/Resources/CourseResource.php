<?php

namespace App\Filament\Teacher\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Course;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use MongoDB\Laravel\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Teacher\Resources\CourseResource\Pages;
use App\Filament\Teacher\Resources\CourseResource\RelationManagers;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Teaching';

    // Labels
    protected static ?string $navigationLabel = 'My Courses';
    protected static ?string $breadcrumb = 'My Courses';
    protected static ?string $pluralModelLabel = 'My Courses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                            if (($get('slug') ?? '') !== Str::slug($old)) {
                                return;
                            }

                            $set('slug', Str::slug($state));
                        }),
                    Forms\Components\TextInput::make('slug'),
                    Forms\Components\TextInput::make('description')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_private')
                        ->label('Private course')
                        ->onIcon('heroicon-m-eye-slash')
                        ->offIcon('heroicon-m-eye')
                        ->inline(false)
                        ->live(true),
                    Forms\Components\TextInput::make('course_key')
                        ->required()
                        ->length(6)
                        ->hidden(fn (Get $get) => $get('is_private') == null)
                        ->suffixActions([
                            Action::make('generate_key')
                                ->icon('heroicon-m-sparkles')
                                ->action(function (Set $set) {
                                    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                                    $key = substr(str_shuffle($chars), 0, 6);
                                    $set('course_key', $key);
                                }),
                            Action::make('copy_key')
                                ->icon('heroicon-m-clipboard-document')
                                ->extraAttributes([
                                    'onclick' => new HtmlString(
                                        '{(() => {' .
                                            'var key = document.getElementById(\'data.course_key\').value;' .
                                            'var tempItem = document.createElement(\'input\');' .
                                            'tempItem.setAttribute(\'display\',\'none\');' .
                                            'tempItem.setAttribute(\'value\',key);' .
                                            'document.body.appendChild(tempItem);' .
                                            'tempItem.select();' .
                                            'document.execCommand(\'Copy\');' .
                                            'tempItem.parentElement.removeChild(tempItem);' .
                                        '})()}'
                                    ),
                                ])
                                ->action(function (Action $action, Get $get) {
                                    if ($get('course_key')) {
                                        Notification::make('copy_course_key')
                                            ->success()
                                            ->title('Copied to clipboard')
                                            ->send();
                                    } else {
                                        Notification::make('fail_copy_course_key')
                                            ->warning()
                                            ->title('Nothing is copied to the clipboard')
                                            ->send();
                                        $action->cancel();
                                    }
                                }),
                        ]),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => auth()->user()->teacher_has_courses())
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
                Tables\Columns\ToggleColumn::make('is_private')
                    ->tooltip(function (Course $record) {
                        if (empty($record->course_key)) {
                            return 'Private course need to set a course key, otherwise student can\'t join by link.';
                        }
                    }),
                Tables\Columns\TextColumn::make('assignments')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->getStateUsing(fn (Model $record) => $record->assignments()->count()),
                Tables\Columns\TextColumn::make('teachers')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->getStateUsing(fn (Model $record) => $record->teachers()->count()),
                Tables\Columns\TextColumn::make('students')
                    ->toggleable()
                    ->getStateUsing(fn (Model $record) => $record->students()->count()),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('copy_invitation_link')
                        ->icon('heroicon-m-clipboard-document')
                        ->extraAttributes(function (Course $record) {
                            $link = route('course.join', $record['_id']);
                            if ($record->is_private && $record->course_key) {
                                $link .= '?course_key='.$record->course_key;
                            }
                            return [
                                'onclick' => new HtmlString(
                                    '{(() => {' .
                                        'var tempItem = document.createElement(\'input\');' .
                                        'tempItem.setAttribute(\'display\',\'none\');' .
                                        'tempItem.setAttribute(\'value\',\''.$link.'\');' .
                                        'document.body.appendChild(tempItem);' .
                                        'tempItem.select();' .
                                        'document.execCommand(\'Copy\');' .
                                        'tempItem.parentElement.removeChild(tempItem);' .
                                    '})()}'
                                ),
                            ];
                        })
                        ->action(function (Tables\Actions\Action $action, Course $record) {
                            if ($record->is_private && empty($record->course_key)) {
                                Notification::make('fail_copy_course_link')
                                    ->warning()
                                    ->title('Set a course key first for Private course')
                                    ->send();
                                $action->cancel();
                            } else {
                                Notification::make('copy_course_link')
                                    ->success()
                                    ->title('Copied to clipboard')
                                    ->send();
                            }
                        }),
                    Tables\Actions\Action::make('add_students')
                        ->icon('heroicon-m-user-group')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('_id')
                                ->hiddenLabel()
                                ->native(false)
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->placeholder('Select a students')
                                ->options(function (Course $record) {
                                    $added = $record->students()->pluck('_id');
                                    return User::whereNotIn('_id', $added)
                                        ->pluck('name', '_id');
                                }),
                        ])
                        ->action(function (Course $record, array $data) {
                            $record->students()->attach($data['_id']);
                            Notification::make('success_add_students')
                                ->success()
                                ->title('Successfully add Students')
                                ->send();
                        })
                        ->closeModalByClickingAway(false),
                ])->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }    
}
