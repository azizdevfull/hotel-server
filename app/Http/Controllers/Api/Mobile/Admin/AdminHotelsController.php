<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Admin\HotelsResource;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AdminHotelsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hotels = Hotel::all();
        return response()->json([
            'status' => true,
            'products' => HotelsResource::collection($hotels)
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
            'region_id' => 'required|exists:regions,id',
            'user_id' => 'required|exists:users,id',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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

        $user = $request->user_id;
        $hotel = new Hotel();
        $hotel->name = $request->name;
        $hotel->price = $request->price;
        $hotel->description = $request->description;
        $hotel->category_id = $request->category_id;
        $hotel->region_id = $request->region_id;
        $hotel->longitude = $request->longitude;
        $hotel->latitude = $request->latitude;
        $hotel->stars = $request->stars;
        $hotel->user_id = $user;
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
            'data' => new HotelsResource($hotel)
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
        return response()->json([
           'status' =>'success',
            'data' => new HotelsResource($hotel)
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
            'user_id' => 'exists:users,id',
            'region_id' => 'exists:regions,id',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'longitude' => 'numeric',
            'latitude' => 'numeric',
            'stars' => 'numeric|between:0,5',
            'views' => 'sometimes|required|integer',
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
    
    
        $hotel->name = $request->name;
        $hotel->price = $request->price;
        $hotel->description = $request->description;
        $hotel->category_id = $request->category_id;
        if($request->user_id) {

            $hotel->user_id = $request->user_id;
        }
        if($request->views) {

            $hotel->views = $request->views;
        }
        if($request->region_id) {

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
