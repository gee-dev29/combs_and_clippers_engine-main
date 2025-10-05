<?php

namespace App\Http\Controllers\App;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;

class BlogController extends Controller
{
    public function index()
    {
        $categoryBlogs = BlogCategory::whereHas('blog', function($query){
            
         return $query->where('status', 'published');

        })
        ->select('id','name')
        ->active()->with('blog:blog_category_id,title,slug,excerpt,cover_image,created_at')->get();

        return response()->json([
            "ResponseStatus" => "Successful",
            'message' => 'Blogs With Category retrieved',
            'data' => $categoryBlogs
        ]);
    }

    public function show($slug)
    {

        $blog = Blog::active()->select('id','title','full_description','slug','cover_image','images','created_at')
        ->where('slug', $slug)->first();
        return response()->json([
            "ResponseStatus" => "Successful",
            'message' => 'Blog retrieved',
            'data' => $blog
        ]);
    }

    public function blogCategories()
    {

        $blogCategories = BlogCategory::active()->select('id', 'name', 'slug')->orderBy('name')->get();
        return response()->json([
            "ResponseStatus" => "Successful",
            'message' => 'Categories retrieved',
            'data' => $blogCategories
        ]);
    }
}
