<?php

namespace App\Filament\Resources;

use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Permission;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use MongoDB\Laravel\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\PermissionResource\Pages;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('name')
                        ->extraInputAttributes(
                            ['style'=>'text-transform: lowercase'], true)
                        ->minLength(2)
                        ->maxLength(255)
                        ->requiredWithout('model_name')
                        ->unique(ignoreRecord: true)
                        ->helperText('To standardize naming, the name will automatically be changed to Lowercase.'),
                ]),
                Section::make('Resource')
                    ->description('You can select permissions from the resources here, instead of inputting them manually.')
                    ->icon('heroicon-o-rectangle-group')
                    ->schema([
                        Select::make('model_name')
                            ->options(function () {
                                $path = app_path('Models') . '/*.php';
                                $models = array();
                                collect(glob($path))->map(function ($file) use (&$models) {
                                    $models[basename($file, '.php')] = basename($file, '.php');
                                });
                                return $models;
                            })
                            ->live(onBlur: true)
                            ->native(false),
                        CheckboxList::make('permissions')
                            ->options([
                                'view' => 'Show',
                                'create' => 'Create',
                                'edit' => 'Update',
                                'delete' => 'Delete',
                            ])
                            ->bulkToggleable()
                            ->hidden(fn (Get $get) => $get('model_name') == null)
                            ->requiredWith('model_name')
                            ->columns(['sm' => 2]),
                    ])
                    ->collapsed()
                    ->hidden(fn (string $operation): bool => $operation === 'edit'),
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
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('model')
                    ->options(function () {
                        $path = app_path('Models') . '/*.php';
                        $models = array();
                        collect(glob($path))->map(function ($file) use (&$models) {
                            $name = basename($file, '.php');
                            $models[Str::lower($name)] = $name;
                        });
                        return $models;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->where('name', 'LIKE', '%'.Str::plural($data['value']).'%')
                            ->orWhere('name', 'LIKE', '%'.$data['value'].'%');
                    }),
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }    
}
