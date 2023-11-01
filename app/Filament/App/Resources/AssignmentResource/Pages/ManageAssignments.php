<?php

namespace App\Filament\App\Resources\AssignmentResource\Pages;

use App\Filament\App\Resources\AssignmentResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAssignments extends ManageRecords
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
