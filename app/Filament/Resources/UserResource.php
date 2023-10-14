<?php

namespace App\Filament\Resources;

use App\Models\Role;
use App\Models\User;
use Filament\Forms\Form;
use App\Models\Permission;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\UserResource\Pages;
use Filament\Tables\Columns\Column;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('name')
                        ->minLength(4)
                        ->required()
                        ->maxLength(32),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),
                    TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(32)
                        ->confirmed(),
                    TextInput::make('password_confirmation')
                        ->password()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(32),
                    Select::make('role_ids')
                        ->label('Roles')
                        ->multiple()
                        ->options(Role::pluck('name', '_id'))
                        ->preload(),
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
        $via_role = (object) array(
            'index' => 0,
            'permissions' => array(),
        );
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->listWithLineBreaks()
                    ->limitList(4)
                    ->toggleable(),
                TextColumn::make('permissions.name')
                    ->label('Direct Permissions')
                    ->listWithLineBreaks()
                    ->limitList(4)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('roles.permissions.name')
                    ->label('Permissions via Roles')
                    ->formatStateUsing(function ($state, Column $column) use (&$via_role) {
                        if ($via_role->index != $column->getRowLoop()->index) {
                            $via_role->index++;
                            $via_role->permissions = array();
                        }
                        if (!in_array($state, $via_role->permissions)) {
                            $via_role->permissions[] = $state;
                            return $state;
                        }
                    })
                    ->listWithLineBreaks()
                    ->limitList(4)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('role_ids')
                    ->label('Role')
                    ->options(Role::pluck('name', '_id'))
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])->tooltip('Actions'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->label('Group Actions'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }    
}
