<?php

namespace App\Filament\Teacher\Resources\CourseResource\Pages;

use App\Filament\Teacher\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
}
