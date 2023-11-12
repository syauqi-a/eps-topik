<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\Choice;
use App\Models\Course;
use App\Models\Permission;
use App\Models\Question;
use App\Models\Role;
use App\Models\User;
use App\Observers\AssignmentObserver;
use App\Observers\ChoiceObserver;
use App\Observers\CourseObserver;
use App\Observers\PermissionObserver;
use App\Observers\QuestionObserver;
use App\Observers\RoleObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
        Course::observe(CourseObserver::class);
        Assignment::observe(AssignmentObserver::class);
        Question::observe(QuestionObserver::class);
        Choice::observe(ChoiceObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
