<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Illuminate\Support\Facades\Config;

class Permission extends Model implements PermissionContract
{
    use HasRoles;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Config::get('auth.defaults.guard');
        parent::__construct($attributes);
    }

    public static function findById(int|string $id, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Config::get('auth.defaults.guard');
        $permission = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (!$permission) {
            throw PermissionDoesNotExist::withId((string) $id, $guardName);
        }

        return $permission;
    }

    public static function findByName(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Config::get('auth.defaults.guard');
        $permission = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (!$permission) {
            // Fallback if named() doesn't exist, though typically it does in Spatie
            throw PermissionDoesNotExist::create($name, $guardName);
        }

        return $permission;
    }

    public static function findOrCreate(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Config::get('auth.defaults.guard');
        $permission = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (!$permission) {
            return static::create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $permission;
    }

    /**
     * A permission can be applied to roles.
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, null, 'permission_ids', 'role_ids');
    }
}
