<?php

namespace App\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class NumberColumn extends TextColumn
{

    public static function make(string $name = 'no', bool $isFromZero = false): static
    {
        return parent::make($name)
            ->rowIndex($isFromZero);
    }
}
