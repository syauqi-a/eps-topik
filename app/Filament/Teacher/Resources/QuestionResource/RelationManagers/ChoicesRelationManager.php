<?php

namespace App\Filament\Teacher\Resources\QuestionResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class ChoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'choices';

    public function form(Form $form): Form
    {
        return $form
            ->schema(static::getChoiceForm());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->columns([
                Tables\Columns\TextColumn::make('text'),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\SelectColumn::make('type')
                    ->options([
                        'text' => 'Text',
                        'image' => 'Image',
                    ])
                    ->selectablePlaceholder(false),
                Tables\Columns\ToggleColumn::make('is_correct'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = $data['is_image'] ? 'image' : 'text';

                        return $data;
                    })
                    ->closeModalByClickingAway(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('Edit')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['is_image'] = $data['type'] == 'image';

                        return $data;
                    })
                    ->form(static::getChoiceForm())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = $data['is_image'] ? 'image' : 'text';

                        return $data;
                    })
                    ->closeModalByClickingAway(false),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getChoiceForm(): array
    {
        return [
            Forms\Components\Grid::make(['default' => 2])
                ->schema([
                    Forms\Components\Toggle::make('is_image')
                        ->onIcon('heroicon-m-photo')
                        ->offIcon('heroicon-m-document-text')
                        ->label(fn (Get $get) => $get('is_image') ? 'Image': 'Text')
                        ->live(),
                    Forms\Components\Toggle::make('is_correct')
                        ->onIcon('heroicon-m-check')
                        ->offIcon('heroicon-m-x-mark')
                        ->live(),
                    Forms\Components\TextInput::make('text')
                        ->hiddenLabel()
                        ->requiredWithout('image')
                        ->hidden(fn (Get $get) => $get('is_image') == true)
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('image')
                        ->hiddenLabel()
                        ->image()
                        ->imageEditor()
                        ->requiredWithout('text')
                        ->hidden(fn (Get $get) => $get('is_image') == false)
                        ->live()
                        // ->disk('s3')
                        ->directory('images/choices')
                        ->visibility('public')
                        ->columnSpanFull(),
                ])
        ];
    }
}
