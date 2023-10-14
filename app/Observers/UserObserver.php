<?php

namespace App\Observers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class UserObserver
{
    private function updateRoles(User $user, bool $is_create): void
    {
        $role_ids = $user->role_ids;

        if ($is_create and empty($role_ids)) {
            // If Role is not assigned, use the default instead
            $user->assignRole('user');
            return;
        }

        $user->roles()->detach();
        
        foreach ($role_ids as $id) {
            $user->assignRole(Role::find($id)->value('name'));
        }
    }

    private function updatePermissions(User $user): void
    {
        $permission_ids = $user->permission_ids;

        if (empty($permission_ids)) {
            return;
        }

        $user->permissions()->detach();

        foreach ($permission_ids as $id) {
            $user->givePermissionTo(Permission::find($id)->value('name'));
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->updateRoles($user, true);
        $this->updatePermissions($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->updateRoles($user, false);
        $this->updatePermissions($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $user->roles()->detach();
        $user->permissions()->detach();
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
