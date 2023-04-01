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
    public function home(Request $request){

        $homeView = HomeView::firstOrCreate(['id' => 1]);
        $homeView->increment('views');
        
        $perPage = $request->get('per_page', 20);
        $hotels = Hotel::paginate($perPage);;
        $categories = Category::all();

        $hotelPaginate = [
            'total' => $hotels->total(),
            'per_page' => $hotels->perPage(),
            'current_page' => $hotels->currentPage(),
            'last_page' => $hotels->lastPage(),
            'next_page_url' => $hotels->nextPageUrl(),
            'prev_page_url' => $hotels->previousPageUrl(),
        ];
    
        return response()->json([
           'status' =>'success',
           'views' =>$homeView->views,
            'hotels' => HotelResource::collection($hotels),
            'hotel_paginate' => $hotelPaginate,
            'categories' => $categories
        ]);
    }
}
