<?php

namespace App\Models;

use MongoDB\Laravel\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Maklad\Permission\Models\Permission as ModelsPermission;

class Permission extends ModelsPermission
{
    use HasFactory;

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            null,
            'permission_ids',
            'role_ids'
        );
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            null,
            'permission_ids',
            'user_ids'
        );
    }
}
