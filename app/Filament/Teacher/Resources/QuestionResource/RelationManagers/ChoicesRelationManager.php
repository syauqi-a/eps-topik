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
            ->schema([
                Forms\Components\TextInput::make('text')
                    ->requiredWithout('image')
                    ->hidden(fn (Get $get) => $get('image'))
                    ->live(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->requiredWithout('text')
                    ->hidden(fn (Get $get) => $get('text'))
                    ->live()
                    // ->disk('s3')
                    ->directory('images/choices')
                    ->visibility('public'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->columns([
                Tables\Columns\TextColumn::make('text'),
                Tables\Columns\ImageColumn::make('image'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
