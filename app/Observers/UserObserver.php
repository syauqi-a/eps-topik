<?php

namespace App\Observers;

use App\Models\User;
use Maklad\Permission\Models\Role;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $role_ids = $user->role_ids;

        if (! $role_ids) {
            // If Role is not assigned, use the default instead
            $user->assignRole('user');
            return;
        }

        $user->roles()->sync([]);

        foreach ($role_ids as $role_id) {
            $user->assignRole(Role::where('_id', $role_id)->value('name'));
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $role_ids = $user->role_ids;
        $user->roles()->detach();

        foreach ($role_ids as $role_id) {
            $role_data = Role::where('_id', $role_id)->get();
            if ($role_data) {
                $user->assignRole($role_data->value('name'));
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
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
        //
    }
}
