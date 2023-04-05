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

    public function show($id){
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ]);
        }
        return response()->json([
            'status' => true,
            'category' => $category,
            'hotels' => $category->hotels
        ]);
    }

}
