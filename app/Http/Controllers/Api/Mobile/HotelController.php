<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\HotelResource;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class HotelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hotels = Hotel::all();
        return response()->json([
           'status' =>'success',
            'data' => HotelResource::collection($hotels)
        ]);
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
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
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
        $hotel->user_id = $user->id;
        // if ($user->product_number <= 0) {
        //     return response([
        //         'status' => false,
        //         'message' => 'User does not have enough money to create a new product'
        //     ], 422);
        // }else{
        //     $user->decrement('product_number');
        // }
        $hotel->save();

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $result = Cloudinary::upload(fopen($photo->getRealPath(), 'r'));
                $hotel->photos()->create([
                    'url' => $result->getSecurePath()
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
