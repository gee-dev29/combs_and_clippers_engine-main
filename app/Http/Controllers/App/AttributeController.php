<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute;

class AttributeController extends Controller
{
    public function index(){
        $attributes= Attribute::all();
        return response()->json(compact('attributes'),200);
    }
}
