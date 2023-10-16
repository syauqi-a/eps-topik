<?php

namespace App\Filament\Resources;

use App\Models\Role;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\RoleResource\Pages;

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
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            $set('name', ucwords(strtolower($state)));
                        })
                        ->disabled(function (Get $get): bool {
                            $role = new Role();
                            return in_array($get('name'), $role->prevent_editing);
                        })
                        ->live(onBlur: true)
                        ->minLength(4)
                        ->maxLength(255)
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('To standardize naming, the name will automatically be changed to Titlecase.'),
                    Select::make('permission_ids')
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
                TextColumn::make('no')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('permissions.name')
                    ->listWithLineBreaks()
                    ->limitList(4),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
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
