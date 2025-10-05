<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageBox extends Model
{
    use HasFactory;
    protected $fillable = ['box_size_id', 'name', 'description_image_url', 'height', 'width', 'length', 'max_weight'];
    protected $table = 'package_boxes';
}
