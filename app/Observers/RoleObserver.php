<?php

namespace App\Observers;

use App\Models\Role;

class RoleObserver
{
    private function updatePermissions(Role $role, bool $is_create): void
    {
        $permission_ids = $role->permission_ids;

        if ($is_create and empty($permission_ids)) {
            return;
        }

        $role->permissions()->detach();

        if (empty($permission_ids)) {
            return;
        }

        foreach ($permission_ids as $id) {
            $role->permissions()->attach($id);
        }
    }

    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        $this->updatePermissions($role, true);
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        $this->updatePermissions($role, false);
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        $role->users()->detach();
        $role->permissions()->detach();
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        $role->users()->detach();
        $role->permissions()->detach();
    }
}
