<?php

namespace App\Models;

use MongoDB\Laravel\Relations\BelongsToMany;
use Maklad\Permission\Models\Role as ModelsRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends ModelsRole
{
    use HasFactory;

    // Prevent record editing
    public $prevent_deleting = ['Super Admin', 'Admin', 'Teacher'];

    // Prevent name editing
    public $prevent_editing = ['Super Admin', 'Admin', 'Teacher'];

    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            null,
            'role_ids',
            'permission_ids'
        );
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            config('permission.models.role'),
            'role_ids',
            'user_ids'
        );
    }

    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|Permission $permissions
     *
     * @return $this
     * @throws GuardDoesNotMatch
     * @throws ReflectionException
     */
    public function givePermissionTo(...$permissions): self
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->each(function ($permission) {
                $this->ensureModelSharesGuard($permission);
            })
            ->all();

        $this->permissions()->saveMany($permissions);

        $this->forgetCachedPermissions();

        return $this;
    }
}
