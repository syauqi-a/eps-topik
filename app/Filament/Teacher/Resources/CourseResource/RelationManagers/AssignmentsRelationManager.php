<?php

namespace App\Filament\Teacher\Resources\CourseResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Assignment;
use Filament\Tables\Table;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Teacher\Resources\AssignmentResource;
use Filament\Resources\RelationManagers\RelationManager;
use Tapp\FilamentTimezoneField\Tables\Filters\TimezoneSelectFilter;
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
                    ->mutateFormDataUsing(function (array $data): array {
                        if ($data['unlimited']) {
                            $data['deadlines'] = [
                                'starts' => null,
                                'ends' => null,
                            ];
                        } else {
                            $data['deadlines'] = [
                                'starts' => CreateAssignment::createDatetime($data['starts']),
                                'ends' => CreateAssignment::createDatetime($data['ends']),
                            ];
                        }

                        $data['created_by'] = [
                            '_id' => auth()->id(),
                            'name' => auth()->user()->name,
                        ];

                        return $data;
                    }),
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
}
