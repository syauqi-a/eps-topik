<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    private function updateRoles(User $user, bool $is_create): void
    {
        $role_ids = $user->role_ids;

        if ($is_create and empty($role_ids)) {
            $user->assignRole('student');
            return;
        }

        $user->roles()->detach();

        if (empty($role_ids)) {
            return;
        }
        
        foreach ($role_ids as $id) {
            $user->roles()->attach($id);
        }
    }

    private function updatePermissions(User $user, bool $is_create): void
    {
        $permission_ids = $user->permission_ids;

        if ($is_create and empty($permission_ids)) {
            return;
        }

        $user->permissions()->detach();

        if (empty($permission_ids)) {
            return;
        }

        foreach ($permission_ids as $id) {
            $user->permissions()->attach($id);
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->updateRoles($user, true);
        $this->updatePermissions($user, true);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->updateRoles($user, false);
        $this->updatePermissions($user, false);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $user->roles()->detach();
        $user->permissions()->detach();
        $user->student_has_courses()->detach();
        $user->teacher_has_courses()->detach();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $user->roles()->detach();
        $user->permissions()->detach();
    }
}
