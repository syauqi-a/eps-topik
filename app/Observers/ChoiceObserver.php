<?php

namespace App\Observers;

use App\Models\Choice;
use Illuminate\Support\Facades\Storage;

class ChoiceObserver
{
    /**
     * Handle the Choice "created" event.
     */
    public function created(Choice $choice): void
    {
        //
    }

    /**
     * Handle the Choice "updated" event.
     */
    public function updated(Choice $choice): void
    {
        //
    }

    /**
     * Handle the Choice "deleted" event.
     */
    public function deleted(Choice $choice): void
    {
        if ($choice->image) {
            Storage::disk('public')->delete($choice->image);
        }
    }

    /**
     * Handle the Choice "restored" event.
     */
    public function restored(Choice $choice): void
    {
        //
    }

    /**
     * Handle the Choice "force deleted" event.
     */
    public function forceDeleted(Choice $choice): void
    {
        //
    }
}
