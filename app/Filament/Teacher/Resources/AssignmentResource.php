<?php

namespace App\Filament\Teacher\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Course;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Assignment;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use MongoDB\Laravel\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Teacher\Resources\AssignmentResource\Pages;
use Tapp\FilamentTimezoneField\Forms\Components\TimezoneSelect;
use Tapp\FilamentTimezoneField\Tables\Filters\TimezoneSelectFilter;
use App\Filament\Teacher\Resources\AssignmentResource\RelationManagers;
use App\Filament\Teacher\Resources\CourseResource\RelationManagers\AssignmentsRelationManager;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Teaching';

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
                    Forms\Components\Fieldset::make('Deadlines')
                        ->schema([
                        Forms\Components\Toggle::make('is_unlimited')
                            ->live(),
                        TimezoneSelect::make('timezone')
                            ->default('Asia/Jakarta')
                            ->native(false)
                            ->searchable()
                            ->live(true)
                            ->hidden(fn (Get $get) => $get('is_unlimited')),
                        Forms\Components\DateTimePicker::make('starts')
                            ->prefix('Starts')
                            ->hiddenLabel()
                            ->timezone(fn (Get $get) => $get('timezone'))
                            ->seconds(false)
                            ->native(false)
                            ->minDate(fn (Get $get) => now($get('timezone')))
                            ->live(true)
                            ->hidden(fn (Get $get) => $get('is_unlimited'))
                            ->required(),
                        Forms\Components\DateTimePicker::make('ends')
                            ->prefix('Ends')
                            ->hiddenLabel()
                            ->timezone(fn (Get $get) => $get('timezone'))
                            ->seconds(false)
                            ->native(false)
                            ->after(fn (Get $get) => $get('starts'))
                            ->minDate(fn (Get $get) => $get('starts'))
                            ->hidden(fn (Get $get) => $get('is_unlimited'))
                            ->required(),
                    ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::getAssignmentTable($table)
            ->query(fn () => Assignment::where('created_by.uid', auth()->id()))
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('assign_to_courses')
                        ->icon('heroicon-m-briefcase')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('_id')
                                ->hiddenLabel()
                                ->native(false)
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->placeholder('Select a courses')
                                ->options(function (Assignment $record) {
                                    $assigned = $record->courses()->pluck('_id');
                                    $uid = auth()->id();
                                    return Course::where('created_by.uid', $uid)
                                        ->whereNotIn('_id', $assigned)
                                        ->pluck('name', '_id');
                                }),
                        ])
                        ->action(function (Assignment $record, array $data) {
                            $record->courses()->attach($data['_id']);
                            Notification::make('success_assign_courses')
                                ->success()
                                ->title('Successfully assigned to Courses')
                                ->send();
                        })
                        ->closeModalByClickingAway(false),
                    Tables\Actions\Action::make('assign_to_students')
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
                                ->options(function (Assignment $record) {
                                    $assigned = $record->students()->pluck('_id');
                                    return User::whereNotIn('_id', $assigned)
                                        ->pluck('name', '_id');
                                }),
                        ])
                        ->action(function (Assignment $record, array $data) {
                            $record->students()->attach($data['_id']);
                            Notification::make('success_assign_students')
                                ->success()
                                ->title('Successfully assigned to Students')
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
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }

    public static function getAssignmentTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Assignment $record): string => Str::limit(
                        ($record->description) ?: '', 40)),
                Tables\Columns\TextColumn::make('deadlines.ends')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('deadlines.ends', $direction);
                    })
                    ->formatStateUsing(function (string $state, Table $table) {
                        if ($state === Carbon::create('9999')->toDateTimeString()) {
                            return 'Unlimited';
                        }

                        $tz = $table->getLivewire()->getTableFilterState('timezone')['value'];
                        $dt = new Carbon($state, $tz);
                        return $dt->toDateTimeString();
                    }),
                Tables\Columns\TextColumn::make('questions')
                    ->formatStateUsing(fn (Assignment $record) => $record
                        ->questions()
                        ->count()
                    )
                    ->default(0)
            ])
            ->filters([
                TimezoneSelectFilter::make('timezone')
                    ->native(false)
                    ->default('Asia/Jakarta')
                    ->searchable()
                    ->query(fn (Assignment $assignment) => $assignment),
                Tables\Filters\SelectFilter::make('course')
                    ->options(function (Table $table) {
                        $user = auth()->user();
                        $courses = $user->student_has_courses()
                            ->pluck('name', '_id')
                            ->toArray();

                        if ($user->isTeacher()) {
                            $courses = array_merge(
                                $courses,
                                $user->teacher_has_courses()
                                    ->pluck('name', '_id')
                                    ->toArray()
                            );
                        }

                        asort($courses);
                        return $courses;
                    })
                    ->native(false)
                    ->searchable()
                    ->query(function (Builder $query, $state) {
                        if ($state['value']) {
                            return $query->where(
                                'course_ids',
                                $state['value']
                            );
                        }
                        return $query;
                    })
                    ->hiddenOn(AssignmentsRelationManager::class),
            ], layout: FiltersLayout::AboveContent)
            ->defaultSort('deadlines.ends');
    }
}
