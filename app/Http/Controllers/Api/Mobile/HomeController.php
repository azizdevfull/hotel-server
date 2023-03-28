<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Hotel;
use App\Models\Category;
use App\Models\HomeView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;

class HomeController extends Controller
{
    public function home(){

        $homeView = HomeView::firstOrCreate(['id' => 1]);
        $homeView->increment('views');
        
        $hotels = Hotel::all();
        $categories = Category::all();
        return response()->json([
           'status' =>'success',
           'views' =>$homeView->views,
            'hotels' => HotelResource::collection($hotels),
            'categories' => $categories
        ]);
    }
}
