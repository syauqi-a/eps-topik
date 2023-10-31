<?php

namespace App\Observers;

use App\Models\Assignment;

class AssignmentObserver
{
    /**
     * Handle the Assignment "created" event.
     */
    public function created(Assignment $assignment): void
    {
        //
    }

    /**
     * Handle the Assignment "updated" event.
     */
    public function updated(Assignment $assignment): void
    {
        //
    }

    /**
     * Handle the Assignment "deleted" event.
     */
    public function deleted(Assignment $assignment): void
    {
        $assignment->courses()->detach();
        $assignment->students()->detach();
    }

    /**
     * Handle the Assignment "restored" event.
     */
    public function restored(Assignment $assignment): void
    {
        //
    }

    /**
     * Handle the Assignment "force deleted" event.
     */
    public function forceDeleted(Assignment $assignment): void
    {
        //
    }
}
