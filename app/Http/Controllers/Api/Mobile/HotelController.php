<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Hotel;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\HotelResource;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\App;

class HotelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::all()->map(function($category) {
            return [
                'id' => $category->id,
                'name' => App::isLocale('ru') ? $category->rus_name : $category->name
            ];
        });

        $perPage = 20;
        $query = $request->query('q');
        $page = intval($request->query('page')) ?? 1;

        $offset = ($page - 1) * $perPage;
        
        $hotels = Hotel::when($query, function($q, $query) {
            return $q->where('name', 'like', "%$query%");
        })
        ->offset($offset)
        ->limit($perPage)
        ->get();
        
        $total = Hotel::when($query, function($q, $query) {
            return $q->where('name', 'like', "%$query%");
        })
        ->count();
        
        $lastPage = ceil($total / $perPage);
    
        $prevPageUrl = $page > 1 ? $request->fullUrlWithQuery(['page' => $page - 1]) : null;
        $nextPageUrl = $page < $lastPage ? $request->fullUrlWithQuery(['page' => $page + 1]) : null;
        
        return response()->json([
            'status' => true,
            'message' => "",
            'data' => [
                'item' => HotelResource::collection($hotels),
                '_links' => [                
                    'prevPageUrl' => $prevPageUrl,
                    'nextPageUrl' => $nextPageUrl
            ],
            '_meta' =>[
                'total' => $total,
                'perPage' => $perPage,
                'currentPage' => $page,
                'lastPage' => $lastPage,
            ]

            ],
            'categories' => $categories
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'region_id' => 'required|exists:regions,id',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'stars' => 'required|numeric|between:0,5',
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $hotel = new Hotel();
        $hotel->name = $request->name;
        $hotel->price = $request->price;
        $hotel->description = $request->description;
        $hotel->category_id = $request->category_id;
        $hotel->region_id = $request->region_id;
        $hotel->longitude = $request->longitude;
        $hotel->latitude = $request->latitude;
        $hotel->stars = $request->stars;
        $hotel->user_id = $user->id;
        if ($user->hotel_number <= 0) {
            return response([
                'status' => false,
                'message' => 'User does not have enough money to create a new hotel'
            ], 422);
        }else if($user->blocked > 0){
            return response([
                'status' => false,
                'message' => 'You are blocked by Admin'
            ], 422);
        }else{
            $user->decrement('hotel_number');
        }
        $hotel->save();

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $result = Cloudinary::upload($photo->getRealPath(), ['folder' => 'hotels']);
                    $public_id = $result->getPublicId();
                    $url = $result->getSecurePath();
                    $hotel->photos()->create([
                        "url"=>$url,
                        "public_id"=>$public_id
                    ]);
                }
            }
        return response([
            'status' => true,
            'message' => 'Hotel created successfully',
            'data' => new HotelResource($hotel)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $hotel = Hotel::find($id);
        if(!$hotel){
            return response()->json([
               'status' => false,
               'message' => 'Hotel not found'
            ], 404);
        }
        $hotel->increment('views');
        return response()->json([
           'status' =>'success',
            'data' => new HotelResource($hotel)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'region_id' => 'exists:regions,id',
            'longitude' => 'numeric',
            'latitude' => 'numeric',
            'stars' => 'numeric|between:0,5',
        ]);
    
        if ($validator->fails()) {
            return response([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }
    
        $hotel = Hotel::find($id);
        if (!$hotel) {
            return response()->json([
                'status' => false,
                'message' => 'Hotel not found'
            ], 404);
        }
    
        $user = Auth::user();
        if ($hotel->user_id !== $user->id) {
            return response([
                'status' => 'error',
                'message' => 'You are not authorized to update this hotel'
            ], 403);
        }
    
        $hotel->name = $request->name;
        $hotel->price = $request->price;
        $hotel->description = $request->description;
        $hotel->category_id = $request->category_id;
        if ($request->region_id) {
            $hotel->region_id = $request->region_id;
        }
        if($request->longitude){

            $hotel->longitude = $request->longitude;
        }
        if($request->latitude){
            $hotel->latitude = $request->latitude;
        }
        if($request->stars){
            $hotel->stars = $request->stars;
        }
        $hotel->save();
    
        if ($request->hasFile('photos')) {
            // Delete old photos
            foreach ($hotel->photos as $photo) {
                Cloudinary::destroy($photo->public_id);
                $photo->delete();
            }
    
            // Upload new photos
                foreach ($request->file('photos') as $photo) {
                    $result = Cloudinary::upload($photo->getRealPath(), ['folder' => 'hotels']);
                    $public_id = $result->getPublicId();
                    $url = $result->getSecurePath();
                    $hotel->photos()->create([
                        "url" => $url,
                        "public_id" => $public_id
                    ]);
                }
        }
    
        return response([
            'status' => true,
            'message' => 'Hotel updated successfully',
        ], 200);
    }
    
    
    
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $hotel = Hotel::find($id);
        if(!$hotel){
            return response()->json([
              'status' => false,
              'message' => 'Hotel not found'
            ], 404);
        }
        $user = Auth::user();

        if ($hotel->user_id !== $user->id) {
            return response([
               'status' => 'error',
               'message' => 'You are not authorized to delete this hotel'
            ], 403);
        }
        foreach ($hotel->photos as $photo) {
            Cloudinary::destroy($photo->public_id);
            $photo->delete();
        }
        $hotel->delete();
        return response([
           'status' => true,
           'message' => 'Hotel deleted successfully'
        ], 200);
    }
}
