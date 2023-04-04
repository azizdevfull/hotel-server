<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserCategoryController extends Controller
{
    public function index(){
        $categories = Category::all();

        return response()->json([
            'status' => true,
            'categories' => $categories
        ]);
    }
}
