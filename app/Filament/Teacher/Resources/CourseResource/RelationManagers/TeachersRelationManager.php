<?php

namespace App\Filament\Teacher\Resources\CourseResource\RelationManagers;

use App\Models\Role;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use MongoDB\Laravel\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Filament\Resources\RelationManagers\RelationManager;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

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
            ->query(fn () => $this->getOwnerRecord()->teachers())
            ->inverseRelationship('teacher_has_courses')
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(function ($state) {
                        $creator = $this->getOwnerRecord()->getAttribute('created_by');
                        $uname = auth()->user()->getAttribute('name');
                        $desc = [];
                        $creator['name']==$state ? $desc[]='owner' : null;
                        $uname==$state ? $desc[]='you' : null;
                        return implode(', ', $desc);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add Teachers')
                    ->modalHeading('Add Teachers')
                    ->recordSelect(function () {
                        return Forms\Components\Select::make('_id')
                            ->hiddenLabel()
                            ->placeholder('Select teachers')
                            ->options(function () {
                                $id = $this->getOwnerRecord()->getAttribute('_id');
                                $teacher_id = Role::where('name', 'Teacher')->pluck('_id')[0];
                                return User::whereNot('teacher_course_ids', $id)
                                    ->where('role_ids', $teacher_id)
                                    ->pluck('name', '_id');
                            })
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->noSearchResultsMessage('No teachers found.')
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
                //
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Kick out')
                    ->modalHeading('Remove selected teachers')
                    ->action(function (
                        Tables\Actions\DetachBulkAction $action,
                        Collection $records,
                        Table $table
                    ) {
                        $owner = $this->getOwnerRecord()->getAttribute('created_by');
                        $relationship = $table->getRelationship();
                        $records->each(function (Model $record) use ($action, $owner, $relationship) {
                            if ($owner['_id'] == $record['_id']) {
                                Notification::make()
                                    ->warning()
                                    ->title('Failed to remove!')
                                    ->body("You cannot remove the course owner.")
                                    ->persistent()
                                    ->send();
                            } else {
                                $relationship->detach($record);
                                $action->success();
                            }
                        });
                    }),
            ]);
    }
}
