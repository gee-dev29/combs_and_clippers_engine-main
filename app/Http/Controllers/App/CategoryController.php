<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\StoreCategory;

class CategoryController extends Controller
{
    public function getProductCategories()
    {
        $productCategories = Category::all();
        return response()->json(compact('productCategories'), 200);
    }


    public function getStoreCategories()
    {
        $storeCategories = StoreCategory::latest('id')->with('subCategories')->get();
        return response()->json(compact('storeCategories'), 200);
    }
}
