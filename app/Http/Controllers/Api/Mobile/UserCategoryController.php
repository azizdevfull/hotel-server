<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;

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
            'hotels' => new HotelResource($category->hotels)
        ]);
    }

}
