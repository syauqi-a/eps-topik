<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        try {
            return $user->hasRole(['super admin', 'admin']);
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission): bool
    {
        try {
            return (
                $user->hasRole(['super admin', 'admin']) or
                $user->hasPermissionTo('view permissions')
            );
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        try {
            return (
                $user->hasRole(['super admin', 'admin']) or
                $user->hasPermissionTo('create permissions')
            );
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission): bool
    {
        try {
            return (
                $user->hasRole(['super admin', 'admin']) or
                $user->hasPermissionTo('edit permissions')
            );
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permission $permission): bool
    {
        try {
            return (
                $user->hasRole(['super admin']) or
                $user->hasPermissionTo('delete permissions')
            );
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Permission $permission): bool
    {
        try {
            return (
                $user->hasRole(['super admin']) or
                $user->hasPermissionTo('delete permissions')
            );
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        try {
            return (
                $user->hasRole(['super admin']) or
                $user->hasPermissionTo('delete permissions')
            );
        } catch (\Throwable $th) {
            return false;
        }
    }
}
