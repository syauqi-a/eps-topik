<?php

namespace App\Filament\Teacher\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Assignment;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use MongoDB\Laravel\Eloquent\Model;
use App\Filament\Teacher\Resources\AssignmentResource\Pages;
use Tapp\FilamentTimezoneField\Forms\Components\TimezoneSelect;
use Tapp\FilamentTimezoneField\Tables\Filters\TimezoneSelectFilter;
use App\Filament\Teacher\Resources\AssignmentResource\RelationManagers;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                        Forms\Components\Toggle::make('unlimited')
                            ->live(),
                        TimezoneSelect::make('timezone')
                            ->default('Asia/Jakarta')
                            ->native(false)
                            ->searchable()
                            ->live(true)
                            ->hidden(fn (Get $get) => $get('unlimited')),
                        Forms\Components\DateTimePicker::make('starts')
                            ->prefix('Starts')
                            ->hiddenLabel()
                            ->timezone(fn (Get $get) => $get('timezone'))
                            ->seconds(false)
                            ->native(false)
                            ->minDate(fn (Get $get) => now($get('timezone')))
                            ->live(true)
                            ->hidden(fn (Get $get) => $get('unlimited'))
                            ->required(),
                        Forms\Components\DateTimePicker::make('ends')
                            ->prefix('Ends')
                            ->hiddenLabel()
                            ->timezone(fn (Get $get) => $get('timezone'))
                            ->seconds(false)
                            ->native(false)
                            ->after(fn (Get $get) => $get('starts'))
                            ->minDate(fn (Get $get) => $get('starts'))
                            ->hidden(fn (Get $get) => $get('unlimited'))
                            ->required(),
                    ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::getCustomTable($table)
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
            //
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

    public static function getCustomTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('deadlines.ends')
                    ->getStateUsing(function (Model $record, Table $table) {
                        $tz = $table->getLivewire()->getTableFilterState('timezone')['value'];
                        $dt = $record->deadlines['ends'];
                        return ($dt) ? new Carbon($dt->toDateTime(), $tz) : 'Unlimited';
                    }),
            ])
            ->filters([
                TimezoneSelectFilter::make('timezone')
                    ->native(false)
                    ->default('Asia/Jakarta')
                    ->searchable()
                    ->query(fn (Assignment $assignment) => $assignment),
            ]);
    }
}