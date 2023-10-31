<x-filament-panels::page>
    @if ($this->hasInfolist())
        {{ $this->infolist }}
    @else
        {{ $this->form }}
    @endif
 
    @if (count($relationManagers = $this->getRelationManagers()) and 
        $this->record->getAttribute('student_ids') and (
            in_array(Auth::id(), $this->record->getAttribute('student_ids')) or
            !($this->record->getAttribute('is_private'))
        )
    )
        <x-filament-panels::resources.relation-managers
            :active-manager="$activeRelationManager"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        />
    @endif
</x-filament-panels::page>
