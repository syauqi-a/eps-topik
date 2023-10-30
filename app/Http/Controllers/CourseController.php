<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CourseController extends Controller
{
    public function join(Request $request, string $course_id): RedirectResponse {
        $course = Course::where('_id', $course_id)->first();
        $course_key = $request->query('course_key');

        $fail_notif = Notification::make('failed')
            ->warning()
            ->body('Invalid course key')
            ->title('Failed to join course');
        $success_notif = Notification::make('successfully')
            ->success()
            ->title('Successfully joined the course');

        if ($course->is_private == false) {
            $course->students()->attach(auth()->id());
            $success_notif->send();
        } elseif (empty($course_key) or empty($course->course_key)) {
            $fail_notif->send();
        } elseif ($course_key == $course->course_key){
            $course->students()->attach(auth()->id());
            $success_notif->send();
        } else {
            $fail_notif->send();
        }
        return redirect('/courses/'.$course_id.($course_key ? '?course_key='.$course_key : ''));
    }
}
