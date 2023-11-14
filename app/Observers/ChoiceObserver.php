<?php

namespace App\Observers;

use App\Models\Choice;
use App\Models\Question;
use Illuminate\Support\Facades\Storage;

class ChoiceObserver
{
    protected function update_count_correct_answer(Question $question): void
    {
        $question->update([
            'count_correct_answers' => $question->choices()
                ->where('is_correct', true)->count()
        ]);
    }

    /**
     * Handle the Choice "created" event.
     */
    public function created(Choice $choice): void
    {
        $this->update_count_correct_answer($choice->question()->first());
    }

    /**
     * Handle the Choice "updated" event.
     */
    public function updated(Choice $choice): void
    {
        $this->update_count_correct_answer($choice->question()->first());
    }

    /**
     * Handle the Choice "deleted" event.
     */
    public function deleted(Choice $choice): void
    {
        if ($choice->image) {
            Storage::disk('public')->delete($choice->image);
        }

        if ($choice->question()->first()) {
            $this->update_count_correct_answer($choice->question()->first());
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
