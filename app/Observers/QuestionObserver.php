<?php

namespace App\Observers;

use App\Models\Question;
use Illuminate\Support\Facades\Storage;

class QuestionObserver
{
    /**
     * Handle the Question "created" event.
     */
    public function created(Question $question): void
    {
        //
    }

    /**
     * Handle the Question "updated" event.
     */
    public function updated(Question $question): void
    {
        //
    }

    /**
     * Handle the Question "deleted" event.
     */
    public function deleted(Question $question)
    {
        $disk = Storage::disk('public');
        if ($question->question_image) {
            $disk->delete($question->question_image);
        }

        if ($question->question_audio) {
            $disk->delete($question->question_audio);
        }
        
        foreach ($question->choices()->get() as $choice) {
            $choice->delete();
        }
    }

    /**
     * Handle the Question "restored" event.
     */
    public function restored(Question $question): void
    {
        //
    }

    /**
     * Handle the Question "force deleted" event.
     */
    public function forceDeleted(Question $question): void
    {
        //
    }
}
