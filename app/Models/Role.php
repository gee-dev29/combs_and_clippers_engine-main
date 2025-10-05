<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the subroles for the role.
     */
    public function subRoles()
    {
        return $this->hasMany(SubRole::class);
    }

    /**
     * Get list of all roles with their subroles.
     * 
     * @return array
     */
    public static function getAvailableRoles()
    {
        $roles = self::with('subRoles')->get();
        $availableRoles = [];

        foreach ($roles as $role) {
            if ($role->subRoles->count() > 0) {
                // Role has subroles
                $subRoles = $role->subRoles->pluck('name')->toArray();
                $availableRoles[$role->name] = $subRoles;
            } else {
                // Role has no subroles
                $availableRoles[$role->name] = [];
            }
        }

        return $availableRoles;
    }
}