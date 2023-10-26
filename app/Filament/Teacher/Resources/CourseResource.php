<?php

namespace App\Filament\Teacher\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Course;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use App\Filament\Teacher\Resources\CourseResource\Pages;
use App\Filament\Teacher\Resources\CourseResource\RelationManagers;
use MongoDB\Laravel\Eloquent\Model;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

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
                        ->inline(false),
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
                Tables\Columns\TextColumn::make('teachers')
                    ->getStateUsing(fn (Model $record) => $record->teachers()->count()),
                Tables\Columns\TextColumn::make('students')
                    ->getStateUsing(fn (Model $record) => $record->students()->count()),
            ])
            ->filters([
                //
            ])
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
            RelationManagers\TeachersRelationManager::class,
            RelationManagers\StudentsRelationManager::class,
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
