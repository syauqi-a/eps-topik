<?php

namespace App\Filament\Resources;

use App\Models\Role;
use Filament\Forms\Form;
use App\Models\Permission;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\RoleResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RoleResource\RelationManagers;
use Filament\Forms\Get;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('name')
                        ->extraInputAttributes(
                            ['style'=>'text-transform: lowercase'], true)
                        ->disabled(function (Get $get, string $operation) {
                            if ($operation != 'edit') {
                                return false;
                            }

                            $role = new Role();
                            return in_array($get('name'), $role->prevent_editing);
                        })
                        ->minLength(2)
                        ->maxLength(255)
                        ->required()
                        ->unique(ignoreRecord: true),
                ]),
                Select::make('permission_ids')
                    ->label('Permissions')
                    ->multiple()
                    ->options(Permission::pluck('name', '_id'))
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('permission_ids')
                    ->label('Permission')
                    ->listWithLineBreaks()
                    ->formatStateUsing(
                        fn (string $state): string => Permission::find($state)
                            ->name
                    )
                    ->limitList(3),
                TextColumn::make('created_at')
                    ->dateTime('d-M-Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (DeleteAction $action, Role $record) {
                            if (in_array($record->name, $record->prevent_deleting)) {
                                Notification::make()
                                    ->warning()
                                    ->title('Failed to delete!')
                                    ->body("You cannot delete the \"{$record->name}\" role.")
                                    ->persistent()
                                    ->send();
                            
                                $action->cancel();
                            }
                        }
                    ),
                ])->tooltip('Actions'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (DeleteBulkAction $action, Collection $records) {
                            $records->each(function (Role $record) use ($action) {
                                if (in_array($record->name, $record->prevent_deleting)) {
                                    Notification::make()
                                        ->warning()
                                        ->title('Failed to delete!')
                                        ->body("You cannot delete the \"{$record->name}\" role.")
                                        ->persistent()
                                        ->send();
                                } else {
                                    $record->delete();
                                    $action->success();
                                }
                            });
                        }
                    ),
                ]),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
