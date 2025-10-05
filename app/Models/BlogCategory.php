<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable= [
        'name',
        'slug',
        'active',
    ];


    public function getNameAttribute($value)
    {
        return ucwords($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::slug($value);

    }

    public function scopeActive($query): Builder
    {
        return $query->where('active', true);
    }

    public function blog()
    {
        return $this->hasMany(Blog::class);
    }

}
