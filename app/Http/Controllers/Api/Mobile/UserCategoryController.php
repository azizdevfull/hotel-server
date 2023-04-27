<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Hotel;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;

class UserCategoryController extends Controller
{
    public function index(){
        $categories = Category::all()->map(function($category) {
            return [
                'id' => $category->id,
                'name' => App::isLocale('ru') ? $category->rus_name : $category->name
            ];
        });

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
                'message' => __('category.not_found'),
            ]);
        }

        if(App::isLocale('ru')){
            $category->name = $category->rus_name;
        }else{
            $category->name = $category->name;
        }

        return response()->json([
            'status' => true,
            'category' => [
              'id' =>  $category->id,
              'name' => $category->name,
              'created_at' => $category->created_at,
              'updated_at' => $category->updated_at
            ],
            'categoryList' => HotelResource::collection($category->hotels)
        ]);
    }

    public function showCategory(Request $request, $id){
        $category = Category::find($id);
    
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => __('category.not_found'),
            ], 404);
        }

        if(App::isLocale('ru')){
            $category->name = $category->rus_name;
        }else{
            $category->name = $category->name;
        }
    
        $perPage = 20;
        $page = intval($request->query('page')) ?? 1;
        $offset = ($page - 1) * $perPage;
    
        $q = $request->query('q');
    
        $query = Hotel::where('category_id', $category->id);
    
        if ($q) {
            $query->where('name', 'like', '%'.$q.'%');
        }
    
        $products = $query->offset($offset)
            ->limit($perPage)
            ->get();
    
        $total = $query->count();
    
        $lastPage = ceil($total / $perPage);
    
        $prevPageUrl = $page > 1 ? $request->fullUrlWithQuery(['page' => $page - 1]) : null;
        $nextPageUrl = $page < $lastPage ? $request->fullUrlWithQuery(['page' => $page + 1]) : null;
    
        return response()->json([
            'status' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
            'message' => "",
            'data' => [
                'item' => HotelResource::collection($products),
                '_links' => [
                    'prevPageUrl' => $prevPageUrl,
                    'nextPageUrl' => $nextPageUrl
                ],
                '_meta' => [
                    'total' => $total,
                    'perPage' => $perPage,
                    'currentPage' => $page,
                    'lastPage' => $lastPage,
                ]
            ]
        ], 200);
    }
    
}
