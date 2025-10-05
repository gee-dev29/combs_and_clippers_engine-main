<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the role that owns the subrole.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}