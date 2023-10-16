<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Maklad\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use MongoDB\Laravel\Relations\BelongsToMany;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Maklad\Permission\Exceptions\GuardDoesNotMatch;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_ids',
        'permission_ids',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        try {
            return (
                $this->hasRole(['Super Admin', 'Admin']) or
                $this->hasPermissionTo('view panels')
            );
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Some user may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            User::class,
            'user_ids',
            'permission_ids',
        );
    }

    /**
     * Some user may be given various roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            User::class,
            'user_ids',
            'role_ids',
        );
    }

    /**
     * Grant the given permission(s) to some user.
     *
     * @param string|array|Permission $permissions
     *
     * @return array|Permission|string
     * @throws GuardDoesNotMatch
     * @throws ReflectionException
     */
    public function givePermissionTo(...$permissions)
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

        return $permissions;
    }
}
