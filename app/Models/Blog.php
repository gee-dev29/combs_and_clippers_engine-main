<?php

namespace App\Models;

use App\Models\Category;
use App\Models\BlogCategory;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'full_description',
        'status',
        'blog_category_id',
        'cover_image',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
        'created_at' => 'datetime:F Y',
    ];

    public function scopeActive($query): Builder
    {
        return $query->where('status', 'published');
    }

    public function blogCategory()
    {
        return $this->belongsTo(BlogCategory::class);
    }
}
