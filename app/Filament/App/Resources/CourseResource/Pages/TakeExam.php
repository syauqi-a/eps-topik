<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\App\Resources\CourseResource;

class TakeExam extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected static ?string $breadcrumb = 'Take Exam';
    protected static ?string $title = 'Take Exam';

    public function getHeading(): string
    {
        return __($this->getModel()::where('_id', request('record'))->first()->name);
    }

    public function fillForm(): void
    {
        //
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('Submit'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('coba'),
            ]);
    }

    public function save(bool $shouldRedirect = true): void
    {
        $this->authorizeAccess();

        // [ ] save process here

        /** @internal Read the DocBlock above the following method. */
        $this->sendSavedNotificationAndRedirect(shouldRedirect: $shouldRedirect);
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Answers saved';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
