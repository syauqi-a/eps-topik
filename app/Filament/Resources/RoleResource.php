<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Role;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Permission;
use Filament\Tables\Table;
use MongoDB\Laravel\Collection;
use Filament\Resources\Resource;
use MongoDB\Laravel\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Filament\Resources\RoleResource\Pages;
use App\Tables\Columns\NumberColumn;

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
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            $set('name', ucwords(strtolower($state)));
                        })
                        ->disabled(function (Get $get): bool {
                            $role = new Role();
                            return in_array(
                                $get('name'), $role->prevent_editing
                            );
                        })
                        ->minLength(4)
                        ->maxLength(255)
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText(
                            'To standardize naming, the name will automatically be changed to Titlecase.'
                        ),
                    Forms\Components\Select::make('permission_ids')
                        ->label('Permissions')
                        ->multiple()
                        ->options(Permission::pluck('name', '_id'))
                        ->preload(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                NumberColumn::make(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->listWithLineBreaks()
                    ->limitList(4),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->before(function (
                            Tables\Actions\DeleteAction $action, Role $record
                        ) {
                            if (in_array(
                                $record->name, $record->prevent_deleting
                            )) {
                                Notification::make()
                                    ->warning()
                                    ->title('Failed to delete!')
                                    ->body(
                                        "You cannot delete the \"{$record->name}\" role."
                                    )
                                    ->persistent()
                                    ->send();

                                $action->cancel();
                            }
                        }
                    ),
                ])->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function (
                        Tables\Actions\DeleteBulkAction $action,
                        Collection $records
                    ) {
                        $records->each(function (Model $record) use ($action) {
                            if (in_array(
                                $record->name, $record->prevent_deleting
                            )) {
                                Notification::make()
                                    ->warning()
                                    ->title('Failed to delete!')
                                    ->body(
                                        "You cannot delete the \"{$record->name}\" role."
                                    )
                                    ->persistent()
                                    ->send();
                            } else {
                                $record->delete();
                                $action->success();
                            }
                        });
                    }),
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
