<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Role;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Permission;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\UserResource\Pages;

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
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->minLength(4)
                        ->required()
                        ->maxLength(32),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(
                            fn (string $state): string => Hash::make($state)
                        )
                        ->dehydrated(
                            fn (?string $state): bool => filled($state)
                        )
                        ->required(function (string $operation): bool {
                            return $operation === 'create';
                        })
                        ->maxLength(32)
                        ->confirmed(),
                    Forms\Components\TextInput::make('password_confirmation')
                        ->password()
                        ->required(function (string $operation): bool {
                            return $operation === 'create';
                        })
                        ->maxLength(32),
                    Forms\Components\Select::make('role_ids')
                        ->label('Roles')
                        ->multiple()
                        ->options(Role::pluck('name', '_id'))
                        ->preload(),
                    Forms\Components\Select::make('permission_ids')
                        ->label('Permissions')
                        ->helperText(
                            new HtmlString(
                                '<b>Users</b> should <i>rarely</i> be given "direct" permissions. Best if Users inherit permissions via the Roles that they\'re assigned to.'
                            )
                        )
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->listWithLineBreaks()
                    ->limitList(4)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->label('Direct Permissions')
                    ->listWithLineBreaks()
                    ->limitList(4)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('roles.permissions.name')
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_ids')
                    ->label('Role')
                    ->options(Role::pluck('name', '_id'))
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
