<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelSearchController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->input('term');
        $hotels = Hotel::search($term);
        $results = [];
    
        foreach ($hotels as $hotel) {
            $results[] = ['value' => new HotelResource($hotel)];
        }
    
        return response()->json($results);
    }
}
