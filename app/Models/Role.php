<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Illuminate\Support\Facades\Config;

class Role extends Model implements RoleContract
{
    use HasPermissions;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Config::get('auth.defaults.guard');
        parent::__construct($attributes);
    }

    public static function findById(int|string $id, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Config::get('auth.defaults.guard');
        $role = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (!$role) {
            throw RoleDoesNotExist::withId((string) $id, $guardName);
        }

        return $role;
    }

    public static function findByName(string $name, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Config::get('auth.defaults.guard');
        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (!$role) {
            throw RoleDoesNotExist::named($name, $guardName);
        }

        return $role;
    }

    public static function findOrCreate(string $name, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Config::get('auth.defaults.guard');
        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (!$role) {
            return static::create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $role;
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id');
    }

    /**
     * A role has many permissions.
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class, null, 'role_ids', 'permission_ids');
    }
}
