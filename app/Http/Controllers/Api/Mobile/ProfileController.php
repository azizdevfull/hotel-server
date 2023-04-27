<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfileController extends Controller
{
    public function Profile()
    {
        $user = Auth::user();
        return response()->json([
            'status' => true,
            'user' => new ProfileResource($user),
        ]);
    }

    public function ProfileUpdate(Request $request)
    {
        $user = Auth::user();

        // Validate the profile photo file
        $request->validate([
            'name' => [
                'sometimes', // Add this to only validate if the field is present in the request
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'avatar' => 'nullable|image|max:2048',
            'username' => [
                'sometimes', // Add this to only validate if the field is present in the request
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('users')->ignore(Auth::user()->id),
            ],
        ]);

        // $user->name = $request->input('name');
        if ($request->input('name')) {
            $user->name = $request->input('name');
        }
        if ($request->input('username')) {
            $user->username = $request->input('username');
        }

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = Str::random(20).'.'.$avatar->getClientOriginalExtension();

            // Delete old avatar if it exists
            if ($user->avatar) {
                Cloudinary::destroy($user->avatar);
            }

            // Upload avatar to Cloudinary
            $uploadedFileUrl = Cloudinary::upload($avatar->getRealPath())->getSecurePath();

            $user->avatar = $uploadedFileUrl;
        }

        $user->save();

        return response()->json([
            'message' => __('auth.update_success'),
            'user' => new ProfileResource($user),
        ], 200);
    }

    public function show($id){
        $user = User::find($id);
        // dd($hotels);
        if (!$user) {
            return response([
                'status' => false,
                'message' => __('auth.user_not_found'),
            ], 404);
        }
        $user->increment('views');
        $hotels = $user->hotels;
        
        return response()->json([
            'status' => true,
            'user' => new ProfileResource($user),
            'hotels' => HotelResource::collection($hotels)
        ]);

    }


}
