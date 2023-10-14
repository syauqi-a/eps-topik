<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super admin', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return (
            $user->hasRole(['super admin', 'admin']) or
            $user->hasPermissionTo('view roles')
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return (
            $user->hasRole(['super admin', 'admin']) or
            $user->hasPermissionTo('create roles')
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        return (
            $user->hasRole(['super admin', 'admin']) or
            $user->hasPermissionTo('edit roles')
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        return (
            $user->hasRole(['super admin']) or
            $user->hasPermissionTo('delete roles')
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return (
            $user->hasRole(['super admin']) or
            $user->hasPermissionTo('delete roles')
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return (
            $user->hasRole(['super admin']) or
            $user->hasPermissionTo('delete roles')
        );
    }
}
