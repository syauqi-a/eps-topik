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
        $uid = auth()->id();

        $redirect_link = redirect('/courses/'.$course_id.($course_key ? '?course_key='.$course_key : ''));

        if ($course->student_ids && in_array($uid, $course->student_ids)) {
            Notification::make('warn')
                ->warning()
                ->title('Already joined the course')
                ->send();
            return $redirect_link;
        }

        $fail_notif = Notification::make('failed')
            ->warning()
            ->title('Failed to join course')
            ->body('Invalid course key');
        $success_notif = Notification::make('successfully')
            ->success()
            ->title('Successfully joined the course');

        if ($course->is_private == false) {
            $course->students()->attach($uid);
            $success_notif->send();
        } elseif (empty($course_key) or empty($course->course_key)) {
            $fail_notif->send();
        } elseif ($course_key == $course->course_key){
            $course->students()->attach($uid);
            $success_notif->send();
        } else {
            $fail_notif->send();
        }

        return $redirect_link;
    }
}
